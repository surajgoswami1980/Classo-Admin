

<?php $__env->startSection('title', 'Stock Movements'); ?>
<?php $__env->startSection('page-title', 'Stock Movements'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <div class="flex justify-end">
        <a href="<?php echo e(panel_route('inventory.index')); ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to inventory</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Type</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Qty</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">By</th>
            </tr></thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600 text-xs"><?php echo e(\Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A')); ?></td>
                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($t->item_name); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->type === 'in'): ?><span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">IN</span>
                        <?php else: ?><span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">OUT</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700"><?php echo e($t->quantity); ?> <?php echo e($t->unit); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($t->reference ?? '—'); ?></td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($t->created_by_name ?? '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No stock movements yet.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transactions->hasPages()): ?><div class="px-4 py-3 border-t"><?php echo e($transactions->links()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\inventory\transactions.blade.php ENDPATH**/ ?>