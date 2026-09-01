
<?php $__env->startSection('title', 'Revenue'); ?>
<?php $__env->startSection('page-title', 'Revenue Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹<?php echo e(number_format($stats['total_revenue'] ?? 0)); ?></p>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-2xl font-bold text-green-600 mt-1">₹<?php echo e(number_format($stats['monthly_revenue'] ?? 0)); ?></p>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <p class="text-sm text-gray-500">Commission Earned</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">₹<?php echo e(number_format($stats['commission'] ?? 0)); ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl border p-6">
        <p class="text-gray-400 text-sm text-center py-8">Revenue chart and detailed breakdown coming soon</p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/super-admin/subscriptions/revenue.blade.php ENDPATH**/ ?>