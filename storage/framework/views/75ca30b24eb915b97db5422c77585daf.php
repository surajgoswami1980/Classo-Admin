<?php $__env->startSection('title', 'Access Denied'); ?>
<?php $__env->startSection('page-title', 'Access Denied'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-50 mb-4">
        <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
    </div>
    <h2 class="text-lg font-semibold text-gray-900">You don't have permission to view this page</h2>
    <p class="text-sm text-gray-500 mt-1 max-w-sm">Ask your school admin to grant you access to this feature if you believe this is a mistake.</p>
    <a href="<?php echo e(panel_route('dashboard')); ?>" class="mt-6 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Back to Dashboard</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/errors/403.blade.php ENDPATH**/ ?>