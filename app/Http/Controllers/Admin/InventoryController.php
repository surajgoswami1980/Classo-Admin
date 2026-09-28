<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Inventory / stock management: categories, items and stock in/out movements,
 * scoped to the current school via current_school_id().
 */
class InventoryController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing inventory.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();
        $filters = $request->only(['search', 'category_id', 'low_stock']);

        $items = DB::table('inventory_items as i')
            ->leftJoin('inventory_categories as c', 'c.id', '=', 'i.category_id')
            ->when($schoolId, fn ($q) => $q->where('i.school_id', $schoolId))
            ->when(!empty($filters['search']), fn ($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('i.name', 'like', "%{$filters['search']}%")->orWhere('i.sku', 'like', "%{$filters['search']}%");
            }))
            ->when(!empty($filters['category_id']), fn ($q) => $q->where('i.category_id', $filters['category_id']))
            ->when(!empty($filters['low_stock']), fn ($q) => $q->whereColumn('i.quantity', '<=', 'i.reorder_level'))
            ->select(['i.*', 'c.name as category_name'])
            ->orderBy('i.name')
            ->paginate(25)
            ->appends($filters);

        $categories = DB::table('inventory_categories')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')->get();

        $stats = DB::table('inventory_items')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(quantity),0) as total_units, COALESCE(SUM(quantity*unit_price),0) as stock_value, SUM(CASE WHEN quantity <= reorder_level THEN 1 ELSE 0 END) as low_stock')
            ->first();

        return view('admin.inventory.index', compact('items', 'categories', 'filters', 'stats'));
    }

    public function storeCategory(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate(['name' => 'required|string|max:100']);

        DB::table('inventory_categories')->insert($validated + ['school_id' => $schoolId, 'created_at' => now()]);

        return back()->with('success', 'Category added.');
    }

    public function storeItem(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('inventory_categories', 'id')->where('school_id', $schoolId)],
            'sku' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            'quantity' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
        ]);

        DB::table('inventory_items')->insert([
            'school_id' => $schoolId,
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'unit' => $validated['unit'] ?? 'pcs',
            'quantity' => $validated['quantity'] ?? 0,
            'reorder_level' => $validated['reorder_level'] ?? 0,
            'unit_price' => $validated['unit_price'] ?? 0,
            'location' => $validated['location'] ?? null,
            'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Item added.');
    }

    public function updateItem(Request $request, $id)
    {
        $schoolId = $this->requireSchoolId();
        abort_unless(DB::table('inventory_items')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('inventory_categories', 'id')->where('school_id', $schoolId)],
            'sku' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:20',
            'reorder_level' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
        ]);

        DB::table('inventory_items')->where('id', $id)->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'unit' => $validated['unit'] ?? 'pcs',
            'reorder_level' => $validated['reorder_level'] ?? 0,
            'unit_price' => $validated['unit_price'] ?? 0,
            'location' => $validated['location'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Item updated.');
    }

    /**
     * Record a stock in/out movement and adjust item quantity.
     */
    public function stock(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'item_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('inventory_items', 'id')->where('school_id', $schoolId)],
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:150',
            'remarks' => 'nullable|string|max:500',
        ]);

        $item = DB::table('inventory_items')->where('id', $validated['item_id'])->first();
        if ($validated['type'] === 'out' && $item->quantity < $validated['quantity']) {
            return back()->withErrors(['error' => "Insufficient stock. Available: {$item->quantity}"]);
        }

        DB::transaction(function () use ($schoolId, $validated, $item) {
            DB::table('inventory_transactions')->insert([
                'school_id' => $schoolId,
                'item_id' => $validated['item_id'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'] ?? $item->unit_price,
                'reference' => $validated['reference'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            $delta = $validated['type'] === 'in' ? $validated['quantity'] : -$validated['quantity'];
            $update = ['quantity' => DB::raw("quantity + ($delta)"), 'updated_at' => now()];
            if ($validated['type'] === 'in' && !empty($validated['unit_price'])) {
                $update['unit_price'] = $validated['unit_price'];
            }
            DB::table('inventory_items')->where('id', $validated['item_id'])->update($update);
        });

        return back()->with('success', 'Stock updated.');
    }

    public function transactions(Request $request)
    {
        $schoolId = $this->schoolId();
        $itemId = $request->get('item_id');

        $transactions = DB::table('inventory_transactions as t')
            ->join('inventory_items as i', 'i.id', '=', 't.item_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
            ->when($schoolId, fn ($q) => $q->where('t.school_id', $schoolId))
            ->when($itemId, fn ($q) => $q->where('t.item_id', $itemId))
            ->select(['t.*', 'i.name as item_name', 'i.unit', 'u.name as created_by_name'])
            ->orderByDesc('t.created_at')
            ->paginate(50)
            ->appends($request->only('item_id'));

        return view('admin.inventory.transactions', compact('transactions'));
    }
}
