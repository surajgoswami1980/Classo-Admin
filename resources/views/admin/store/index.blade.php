@extends('layouts.app')

@section('title', 'Store')
@section('page-title', 'School Store')

@section('content')
<div x-data="storeManager()" class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Items</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['items'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Published</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Awaiting Collection</p><p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['purchased'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Revenue</p><p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($stats['revenue']) }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Store Items</h2>
            <p class="text-sm text-gray-500">Items students can buy in the app. Payment is online; students collect at the counter.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ panel_route('store.orders') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">View Orders</a>
            <button @click="openCreate()" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition active:scale-95">+ Add Item</button>
        </div>
    </div>

    {{-- Items grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($items as $item)
        <div class="group bg-white rounded-xl border overflow-hidden hover:shadow-md transition-all duration-200">
            <div class="relative h-40 bg-gray-100 overflow-hidden">
                @if($item->image)
                    <img src="{{ \Illuminate\Support\Str::startsWith($item->image,'http') ? $item->image : asset('storage/'.$item->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $item->name }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    </div>
                @endif
                <span class="absolute top-2 right-2 text-xs font-medium px-2 py-0.5 rounded-full {{ $item->is_active ? 'text-green-700 bg-green-100' : 'text-gray-600 bg-gray-200' }}">
                    {{ $item->is_active ? 'Live' : 'Hidden' }}
                </span>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                        @if($item->category)<p class="text-xs text-gray-400 capitalize">{{ $item->category }}</p>@endif
                    </div>
                    <p class="text-lg font-bold text-blue-600 whitespace-nowrap">₹{{ number_format($item->price) }}</p>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ is_null($item->stock) ? 'Unlimited stock' : $item->stock.' in stock' }}</p>
                <div class="flex items-center gap-2 mt-3 pt-3 border-t">
                    <button @click='openEdit(@json($item))' class="flex-1 text-xs font-medium text-blue-600 hover:bg-blue-50 py-1.5 rounded-lg transition">Edit</button>
                    <form method="POST" action="{{ panel_route('store.toggle', $item->id) }}" class="flex-1">@csrf @method('PATCH')
                        <button type="submit" class="w-full text-xs font-medium text-gray-600 hover:bg-gray-100 py-1.5 rounded-lg transition">{{ $item->is_active ? 'Hide' : 'Publish' }}</button>
                    </form>
                    <form method="POST" action="{{ panel_route('store.destroy', $item->id) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-red-500 hover:bg-red-50 px-2 py-1.5 rounded-lg transition">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl border p-12 text-center text-gray-400">
            No store items yet. Click "Add Item" to list your first product.
        </div>
        @endforelse
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="show" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="show=false">
        <div x-show="show" x-transition.scale.origin.center class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Item' : 'Add Item'"></h3>
            <form :action="editing ? '{{ url(panel_prefix().'/store') }}/' + form.id : '{{ panel_route('store.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Item Name *</label>
                    <input type="text" name="name" x-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="description" x-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <input type="text" name="category" x-model="form.category" placeholder="books, uniform..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Price (₹) *</label>
                        <input type="number" name="price" x-model="form.price" min="0" step="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stock (blank = unlimited)</label>
                        <input type="number" name="stock" x-model="form.stock" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" x-model="form.sort_order" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Item Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-xs">
                    <p class="text-[11px] text-gray-400 mt-1" x-show="editing">Leave blank to keep the current image.</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300"> Publish (visible to students)
                </label>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="show=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm" x-text="editing ? 'Update' : 'Add Item'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function storeManager() {
        const blank = () => ({ id:'', name:'', description:'', category:'', price:'', stock:'', sort_order:0, is_active:true });
        return {
            show: false, editing: false,
            // NOTE: initialise `form` with a plain object (not `this.blank()`),
            // because inside an object literal `this` is NOT the component yet.
            form: blank(),
            openCreate() { this.editing=false; this.form=blank(); this.show=true; },
            openEdit(i) { this.editing=true; this.form={ id:i.id, name:i.name||'', description:i.description||'', category:i.category||'', price:i.price||'', stock:(i.stock??''), sort_order:i.sort_order||0, is_active:!!i.is_active }; this.show=true; },
        };
    }
</script>
@endpush
@endsection
