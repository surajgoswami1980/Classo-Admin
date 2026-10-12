<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * School Store admin: manage sellable items and fulfil orders. Students buy
 * and pay online (invoice/order created as "purchased" = paid, not collected).
 * When the student collects the item at the counter, admin marks it "completed".
 */
class StoreController extends Controller
{
    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing the store.');

        return $schoolId;
    }

    // ─── Items ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $items = DB::table('store_items')
            ->where('school_id', $schoolId)
            ->orderBy('sort_order')->orderByDesc('id')
            ->get();

        $stats = [
            'items' => $items->count(),
            'active' => $items->where('is_active', true)->count(),
            'purchased' => (int) DB::table('store_orders')->where('school_id', $schoolId)->where('status', 'purchased')->count(),
            'revenue' => (float) DB::table('store_orders')->where('school_id', $schoolId)->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('admin.store.index', compact('items', 'stats'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $data = $this->validateItem($request);
        $data['image'] = $this->storeUpload($request, $schoolId);
        $data['school_id'] = $schoolId;
        $data['created_by'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        DB::table('store_items')->insert($data);

        return back()->with('success', 'Store item added.');
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->requireSchoolId();
        $item = DB::table('store_items')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($item, 404);

        $data = $this->validateItem($request);
        $newImage = $this->storeUpload($request, $schoolId);
        if ($newImage) {
            $this->deleteFile($item->image);
            $data['image'] = $newImage;
        }
        $data['updated_at'] = now();

        DB::table('store_items')->where('id', $id)->update($data);

        return back()->with('success', 'Store item updated.');
    }

    public function destroy($id)
    {
        $schoolId = $this->requireSchoolId();
        $item = DB::table('store_items')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($item, 404);
        $this->deleteFile($item->image);
        DB::table('store_items')->where('id', $id)->delete();

        return back()->with('success', 'Store item deleted.');
    }

    public function toggle($id)
    {
        $schoolId = $this->requireSchoolId();
        $item = DB::table('store_items')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($item, 404);
        DB::table('store_items')->where('id', $id)->update(['is_active' => !$item->is_active, 'updated_at' => now()]);

        return back()->with('success', 'Item ' . ($item->is_active ? 'hidden' : 'published') . '.');
    }

    // ─── Orders ──────────────────────────────────────────────────────────

    public function orders(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $filters = $request->only(['status']);

        $orders = DB::table('store_orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('students as s', 's.id', '=', 'o.student_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->where('o.school_id', $schoolId)
            ->when(!empty($filters['status']), fn ($q) => $q->where('o.status', $filters['status']))
            ->select([
                'o.*', 'u.name as buyer_name', 'u.phone as buyer_phone',
                's.roll_number', 'c.name as class_name', 'sec.name as section_name',
            ])
            ->orderByDesc('o.created_at')
            ->paginate(20)
            ->appends($filters);

        $stats = [
            'purchased' => (int) DB::table('store_orders')->where('school_id', $schoolId)->where('status', 'purchased')->count(),
            'completed' => (int) DB::table('store_orders')->where('school_id', $schoolId)->where('status', 'completed')->count(),
            'revenue' => (float) DB::table('store_orders')->where('school_id', $schoolId)->where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('admin.store.orders', compact('orders', 'filters', 'stats'));
    }

    /**
     * Mark a paid order as collected/completed (student picked up the item).
     */
    public function complete($id)
    {
        $schoolId = $this->requireSchoolId();
        $order = DB::table('store_orders')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($order, 404);

        if ($order->status !== 'purchased') {
            return back()->with('error', 'Only paid (purchased) orders can be completed.');
        }

        DB::table('store_orders')->where('id', $id)->update([
            'status' => 'completed',
            'completed_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Order marked as collected/completed.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function validateItem(Request $request): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        return [
            'name' => $v['name'],
            'description' => $v['description'] ?? null,
            'category' => $v['category'] ?? null,
            'price' => $v['price'],
            'stock' => $v['stock'] ?? null,
            'sort_order' => $v['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function storeUpload(Request $request, int $schoolId): ?string
    {
        if (!$request->hasFile('image')) return null;
        return $request->file('image')->store("store/{$schoolId}", 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
