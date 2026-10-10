

<?php $__env->startSection('title', 'Event Registrations'); ?>
<?php $__env->startSection('page-title', 'Registrations — ' . $event->title); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900"><?php echo e($event->title); ?></h2>
            <p class="text-sm text-gray-500">
                <?php echo e(\Carbon\Carbon::parse($event->start_at)->format('d M Y, h:i A')); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->venue): ?> · <?php echo e($event->venue); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->is_paid): ?> · Fee ₹<?php echo e(number_format($event->fee)); ?> <?php else: ?> · Free <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>
        <a href="<?php echo e(panel_route('events.index')); ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to events</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b text-sm font-medium text-gray-600"><?php echo e($registrations->count()); ?> registration(s)</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Class</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Contact</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Payment</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($r->user_name); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r->roll_number): ?><p class="text-xs text-gray-400">Roll <?php echo e($r->roll_number); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($r->class_name ?? '—'); ?> <?php echo e($r->section_name ? '- '.$r->section_name : ''); ?></td>
                    <td class="px-4 py-3 text-gray-600 text-xs"><?php echo e($r->phone ?? $r->email ?? '—'); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php $pb = ['not_required'=>'text-gray-600 bg-gray-100','pending'=>'text-amber-700 bg-amber-50','paid'=>'text-green-700 bg-green-50','failed'=>'text-red-700 bg-red-50'][$r->payment_status] ?? 'text-gray-600 bg-gray-100'; ?>
                        <span class="text-xs font-medium <?php echo e($pb); ?> px-2 py-0.5 rounded-full">
                            <?php echo e($r->payment_status === 'not_required' ? 'Free' : ucfirst($r->payment_status)); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r->amount > 0): ?> ₹<?php echo e(number_format($r->amount)); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php $sb = ['registered'=>'text-blue-700 bg-blue-50','cancelled'=>'text-red-700 bg-red-50','attended'=>'text-green-700 bg-green-50'][$r->status] ?? 'text-gray-600 bg-gray-100'; ?>
                        <span class="text-xs font-medium <?php echo e($sb); ?> px-2 py-0.5 rounded-full"><?php echo e(ucfirst($r->status)); ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e(\Carbon\Carbon::parse($r->created_at)->format('d M Y, h:i A')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No registrations yet.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\events\registrations.blade.php ENDPATH**/ ?>