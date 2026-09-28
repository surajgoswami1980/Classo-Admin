@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory & Stock')

@section('content')
<div x-data="{
        showItem:false, showCategory:false, showStock:false, editing:false,
        stockType:'in', stockItemId:'', stockItemName:'',
        form:{ id:'', name:'', category_id:'', sku:'', unit:'pcs', quantity:0, reorder_level:0, unit_price:0, location:'' },
        openCreate(){ this.editing=false; this.form={ id:'', name:'', category_id:'', sku:'', unit:'pcs', quantity:0, reorder_level:0, unit_price:0, location:'' }; this.showItem=true; },
        openEdit(i){ this.editing=true; this.form={ ...i }; this.showItem=true; },
        openStock(id,name,type){ this.stockItemId=id; this.stockItemName=name; this.stockType=type; this.showStock=true; },
     }" class="space-y-6">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Items</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats->total_items ?? 0 }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Units</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats->total_units ?? 0) }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Stock Value</p><p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($stats->stock_value ?? 0) }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Low Stock</p><p class="text-2xl font-bold text-red-600 mt-1">{{ $stats->low_stock ?? 0 }}</p></div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search item / SKU" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" {{ ($filters['category_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
            <label class="inline-flex items-center gap-1 text-sm text-gray-600"><input type="checkbox" name="low_stock" value="1" {{ !empty($filters['low_stock']) ? 'checked' : '' }} class="rounded border-gray-300"> Low stock</label>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ panel_route('inventory.transactions') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Movements</a>
            <button @click="showCategory=true" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">+ Category</button>
            <button @click="openCreate()" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Item</button>
        </div>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Stock</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Unit Price</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Location</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $item->name }}</p>
                        @if($item->sku)<p class="text-xs text-gray-400">SKU: {{ $item->sku }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->category_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $item->quantity <= $item->reorder_level ? 'text-red-700 bg-red-50' : 'text-gray-700 bg-gray-100' }}">{{ $item->quantity }} {{ $item->unit }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->location ?? '—' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click="openStock({{ $item->id }}, @js($item->name), 'in')" class="text-xs font-medium text-green-600 hover:underline">In</button>
                        <button @click="openStock({{ $item->id }}, @js($item->name), 'out')" class="text-xs font-medium text-amber-600 hover:underline ml-3">Out</button>
                        <button @click='openEdit(@json($item))' class="text-xs font-medium text-blue-600 hover:underline ml-3">Edit</button>
                    </td>
                </tr>
                @empty<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No items yet.</td></tr>@endforelse
            </tbody>
        </table>
        @if($items->hasPages())<div class="px-4 py-3 border-t">{{ $items->links() }}</div>@endif
    </div>

    {{-- Category Modal --}}
    <div x-show="showCategory" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCategory=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Category</h3>
            <form method="POST" action="{{ panel_route('inventory.categories.store') }}" class="space-y-4">@csrf
                <input type="text" name="name" required placeholder="Category name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <div class="flex justify-end gap-2"><button type="button" @click="showCategory=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Add</button></div>
            </form>
        </div>
    </div>

    {{-- Item Modal --}}
    <div x-show="showItem" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showItem=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Item' : 'Add Item'"></h3>
            <form method="POST" :action="editing ? '{{ url(panel_prefix().'/inventory/items') }}/' + form.id : '{{ panel_route('inventory.items.store') }}'" class="space-y-4">@csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Name *</label><input type="text" name="name" x-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select name="category_id" x-model="form.category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">None</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">SKU</label><input type="text" name="sku" x-model="form.sku" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Unit</label><input type="text" name="unit" x-model="form.unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div x-show="!editing"><label class="block text-xs font-medium text-gray-600 mb-1">Opening Qty</label><input type="number" name="quantity" x-model="form.quantity" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Reorder Level</label><input type="number" name="reorder_level" x-model="form.reorder_level" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Unit Price</label><input type="number" name="unit_price" x-model="form.unit_price" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Location</label><input type="text" name="location" x-model="form.location" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div class="flex justify-end gap-2"><button type="button" @click="showItem=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm" x-text="editing ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>

    {{-- Stock Modal --}}
    <div x-show="showStock" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showStock=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Stock <span x-text="stockType==='in' ? 'In' : 'Out'"></span></h3>
            <p class="text-sm text-gray-500 mb-4" x-text="stockItemName"></p>
            <form method="POST" action="{{ panel_route('inventory.stock') }}" class="space-y-4">@csrf
                <input type="hidden" name="item_id" :value="stockItemId">
                <input type="hidden" name="type" :value="stockType">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Quantity *</label><input type="number" name="quantity" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div x-show="stockType==='in'"><label class="block text-xs font-medium text-gray-600 mb-1">Unit Price</label><input type="number" name="unit_price" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1" x-text="stockType==='in' ? 'Supplier / Bill No.' : 'Issued To'"></label><input type="text" name="reference" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Remarks</label><textarea name="remarks" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showStock=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
