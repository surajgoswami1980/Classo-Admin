

<?php $__env->startSection('title', 'Issued Books'); ?>
<?php $__env->startSection('page-title', 'Issued Books'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <a href="<?php echo e(panel_route('library.issues')); ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e(empty($filters['filter']) && empty($filters['status']) ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'); ?>">All</a>
            <a href="<?php echo e(panel_route('library.issues', ['status' => 'issued'])); ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e(($filters['status'] ?? '') === 'issued' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'); ?>">Currently Issued</a>
            <a href="<?php echo e(panel_route('library.issues', ['filter' => 'overdue'])); ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e(($filters['filter'] ?? '') === 'overdue' ? 'bg-red-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'); ?>">Overdue</a>
            <a href="<?php echo e(panel_route('library.issues', ['filter' => 'expiring'])); ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e(($filters['filter'] ?? '') === 'expiring' ? 'bg-amber-500 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'); ?>">Expiring (7d)</a>
            <a href="<?php echo e(panel_route('library.issues', ['status' => 'returned'])); ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium <?php echo e(($filters['status'] ?? '') === 'returned' ? 'bg-gray-800 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'); ?>">Returned</a>
        </div>
        <a href="<?php echo e(panel_route('library.index')); ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to catalog</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Book</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Borrower</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Issued</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Due</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Days Left</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Fine</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($issue->book_title); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($issue->book_author); ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <?php echo e($issue->borrower_name ?? '—'); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->roll_number): ?><span class="text-xs text-gray-400 block">Roll <?php echo e($issue->roll_number); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e(\Carbon\Carbon::parse($issue->issue_date)->format('d M Y')); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e(\Carbon\Carbon::parse($issue->due_date)->format('d M Y')); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->status === 'returned'): ?>
                            <span class="text-gray-400">—</span>
                        <?php elseif($issue->is_overdue): ?>
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full"><?php echo e(abs($issue->days_to_expire)); ?>d overdue</span>
                        <?php else: ?>
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e($issue->days_to_expire); ?>d</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right <?php echo e($issue->current_fine > 0 ? 'text-red-600 font-medium' : 'text-gray-500'); ?>">
                        ₹<?php echo e(number_format($issue->current_fine, 2)); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->fine_paid): ?><span class="text-[10px] text-green-600 block">paid</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->status === 'returned'): ?>
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">Returned</span>
                        <?php else: ?>
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Issued</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->status !== 'returned'): ?>
                        <form method="POST" action="<?php echo e(panel_route('library.issues.return', $issue->id)); ?>" class="inline" onsubmit="return confirm('Mark this book as returned?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs font-medium text-teal-600 hover:underline">Return</button>
                        </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->fine_amount > 0 && !$issue->fine_paid): ?>
                        <form method="POST" action="<?php echo e(panel_route('library.issues.fine-paid', $issue->id)); ?>" class="inline ml-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline">Mark Fine Paid</button>
                        </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No records found.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issues->hasPages()): ?>
        <div class="px-4 py-3 border-t"><?php echo e($issues->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\library\issues.blade.php ENDPATH**/ ?>