

<?php $__env->startSection('title', 'Inventory'); ?>
<?php $__env->startSection('page-title', 'Inventory & Stock'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="{
        showItem:false, showCategory:false, showStock:false, editing:false,
        stockType:'in', stockItemId:'', stockItemName:'',
        form:{ id:'', name:'', category_id:'', sku:'', unit:'pcs', quantity:0, reorder_level:0, unit_price:0, location:'' },
        openCreate(){ this.editing=false; this.form={ id:'', name:'', category_id:'', sku:'', unit:'pcs', quantity:0, reorder_level:0, unit_price:0, location:'' }; this.showItem=true; },
        openEdit(i){ this.editing=true; this.form={ ...i }; this.showItem=true; },
        openStock(id,name,type){ this.stockItemId=id; this.stockItemName=name; this.stockType=type; this.showStock=true; },
     }" class="space-y-6">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Items</p><p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($stats->total_items ?? 0); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Units</p><p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e(number_format($stats->total_units ?? 0)); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Stock Value</p><p class="text-2xl font-bold text-green-600 mt-1">₹<?php echo e(number_format($stats->stock_value ?? 0)); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Low Stock</p><p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($stats->low_stock ?? 0); ?></p></div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search item / SKU" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All categories</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php echo e(($filters['category_id'] ?? '') == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <label class="inline-flex items-center gap-1 text-sm text-gray-600"><input type="checkbox" name="low_stock" value="1" <?php echo e(!empty($filters['low_stock']) ? 'checked' : ''); ?> class="rounded border-gray-300"> Low stock</label>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(panel_route('inventory.transactions')); ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Movements</a>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($item->name); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->sku): ?><p class="text-xs text-gray-400">SKU: <?php echo e($item->sku); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($item->category_name ?? '—'); ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($item->quantity <= $item->reorder_level ? 'text-red-700 bg-red-50' : 'text-gray-700 bg-gray-100'); ?>"><?php echo e($item->quantity); ?> <?php echo e($item->unit); ?></span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">₹<?php echo e(number_format($item->unit_price, 2)); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($item->location ?? '—'); ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click="openStock(<?php echo e($item->id); ?>, <?php echo \Illuminate\Support\Js::from($item->name)->toHtml() ?>, 'in')" class="text-xs font-medium text-green-600 hover:underline">In</button>
                        <button @click="openStock(<?php echo e($item->id); ?>, <?php echo \Illuminate\Support\Js::from($item->name)->toHtml() ?>, 'out')" class="text-xs font-medium text-amber-600 hover:underline ml-3">Out</button>
                        <button @click='openEdit(<?php echo json_encode($item, 15, 512) ?>)' class="text-xs font-medium text-blue-600 hover:underline ml-3">Edit</button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No items yet.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->hasPages()): ?><div class="px-4 py-3 border-t"><?php echo e($items->links()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div x-show="showCategory" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCategory=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Category</h3>
            <form method="POST" action="<?php echo e(panel_route('inventory.categories.store')); ?>" class="space-y-4"><?php echo csrf_field(); ?>
                <input type="text" name="name" required placeholder="Category name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <div class="flex justify-end gap-2"><button type="button" @click="showCategory=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Add</button></div>
            </form>
        </div>
    </div>

    
    <div x-show="showItem" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showItem=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Item' : 'Add Item'"></h3>
            <form method="POST" :action="editing ? '<?php echo e(url(panel_prefix().'/inventory/items')); ?>/' + form.id : '<?php echo e(panel_route('inventory.items.store')); ?>'" class="space-y-4"><?php echo csrf_field(); ?>
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Name *</label><input type="text" name="name" x-model="form.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select name="category_id" x-model="form.category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">None</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    
    <div x-show="showStock" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showStock=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Stock <span x-text="stockType==='in' ? 'In' : 'Out'"></span></h3>
            <p class="text-sm text-gray-500 mb-4" x-text="stockItemName"></p>
            <form method="POST" action="<?php echo e(panel_route('inventory.stock')); ?>" class="space-y-4"><?php echo csrf_field(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\inventory\index.blade.php ENDPATH**/ ?>