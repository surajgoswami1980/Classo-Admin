

<?php $__env->startSection('title', 'Student Books'); ?>
<?php $__env->startSection('page-title', 'Library Books — ' . ($student->user_name ?? 'Student')); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900"><?php echo e($student->user_name); ?></h2>
            <p class="text-sm text-gray-500"><?php echo e($student->class_name); ?> - <?php echo e($student->section_name); ?> · Roll <?php echo e($student->roll_number ?? '—'); ?></p>
        </div>
        <a href="<?php echo e(panel_route('students.index')); ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Book</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Issued</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Due</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Fine</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($issue->book_title); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($issue->book_author); ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e(\Carbon\Carbon::parse($issue->issue_date)->format('d M Y')); ?></td>
                    <td class="px-4 py-3 text-gray-600">
                        <?php echo e(\Carbon\Carbon::parse($issue->due_date)->format('d M Y')); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->is_overdue): ?><span class="text-xs text-red-600 block"><?php echo e(abs($issue->days_to_expire)); ?>d overdue</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->status === 'returned'): ?>
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">Returned</span>
                        <?php elseif($issue->is_overdue): ?>
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">Overdue</span>
                        <?php else: ?>
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Issued</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right <?php echo e($issue->current_fine > 0 ? 'text-red-600 font-medium' : 'text-gray-500'); ?>">
                        ₹<?php echo e(number_format($issue->current_fine, 2)); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($issue->fine_paid): ?><span class="text-[10px] text-green-600 block">paid</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No books issued to this student.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\students\books.blade.php ENDPATH**/ ?>