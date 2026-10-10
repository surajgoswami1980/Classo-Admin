
<?php $__env->startSection('title', 'Subscription: ' . $school->name); ?>
<?php $__env->startSection('page-title', 'Subscription Details'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl space-y-6">

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900"><?php echo e($school->name); ?></h3>
                <p class="text-xs text-gray-400 font-mono"><?php echo e($school->code); ?></p>
            </div>
            <a href="<?php echo e(route('admin.subscriptions.edit', $school)); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Edit</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div>
                <p class="text-xs text-gray-500">Plan</p>
                <p class="text-sm font-medium text-gray-900 capitalize mt-1"><?php echo e($school->subscription_plan ?? '—'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <p class="text-sm font-medium mt-1 <?php echo e($school->is_active ? 'text-green-600' : 'text-gray-500'); ?>"><?php echo e($school->is_active ? 'Active' : 'Inactive'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Start</p>
                <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e($school->subscription_start ? \Carbon\Carbon::parse($school->subscription_start)->format('d M Y') : '—'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">End</p>
                <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e($school->subscription_end ? \Carbon\Carbon::parse($school->subscription_end)->format('d M Y') : '—'); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Max Students</p>
                <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e(number_format($school->max_students ?? 0)); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Max Staff</p>
                <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e(number_format($school->max_staff ?? 0)); ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹<?php echo e(number_format($revenue['total'])); ?></p>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Platform Commission</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">₹<?php echo e(number_format($revenue['commission'])); ?></p>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Successful Transactions</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($revenue['transactions'])); ?></p>
        </div>
    </div>

    <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="inline-block text-sm text-gray-600 hover:underline">&larr; Back to subscriptions</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\super-admin\subscriptions\show.blade.php ENDPATH**/ ?>