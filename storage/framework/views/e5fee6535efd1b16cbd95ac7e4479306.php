

<?php $__env->startSection('title', 'Student Transport'); ?>
<?php $__env->startSection('page-title', 'Transport — ' . ($student->user_name ?? 'Student')); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-5 max-w-2xl">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900"><?php echo e($student->user_name); ?></h2>
            <p class="text-sm text-gray-500"><?php echo e($student->class_name); ?> - <?php echo e($student->section_name); ?> · Roll <?php echo e($student->roll_number ?? '—'); ?></p>
        </div>
        <a href="<?php echo e(panel_route('students.index')); ?>" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment): ?>
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="bg-gradient-to-br from-teal-600 to-teal-700 p-5 text-white">
            <p class="text-teal-100 text-xs">Assigned Route</p>
            <h3 class="text-xl font-bold"><?php echo e($assignment->route_name); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->session_name): ?><p class="text-teal-100 text-xs mt-1">Session: <?php echo e($assignment->session_name); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Stop</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->stop_name); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pickup / Drop</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->pickup_time ?? '—'); ?> / <?php echo e($assignment->drop_time ?? '—'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Vehicle</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->vehicle_number ? $assignment->vehicle_number.' ('.$assignment->vehicle_type.')' : 'Not assigned'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Capacity</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->capacity ?? '—'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Driver</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->driver_name ?? 'Not assigned'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->driver_phone): ?><span class="text-xs text-gray-400">· <?php echo e($assignment->driver_phone); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Conductor</p>
                <p class="text-sm font-medium text-gray-900"><?php echo e($assignment->conductor_name ?? 'Not assigned'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assignment->conductor_phone): ?><span class="text-xs text-gray-400">· <?php echo e($assignment->conductor_phone); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl border p-10 text-center text-gray-400">
        This student is not assigned to any transport route.
        <div class="mt-3">
            <a href="<?php echo e(panel_route('transport.index')); ?>" class="text-blue-600 text-sm hover:underline">Go to Transport →</a>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\students\transport.blade.php ENDPATH**/ ?>