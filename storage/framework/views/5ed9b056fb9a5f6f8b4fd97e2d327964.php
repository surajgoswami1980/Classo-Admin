
<?php $__env->startSection('title', $school->name); ?>
<?php $__env->startSection('page-title', $school->name); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl">
    <div class="bg-white rounded-xl border p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                    <?php echo e(strtoupper(substr($school->name, 0, 1))); ?>

                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($school->name); ?></h2>
                    <p class="text-sm text-gray-500">Code: <span class="font-mono bg-gray-100 px-2 py-0.5 rounded"><?php echo e($school->code); ?></span></p>
                </div>
            </div>
            <div class="flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school->is_active): ?>
                <form method="POST" action="<?php echo e(route('admin.schools.switch', $school)); ?>" class="inline"><?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 border border-blue-300 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-50 transition">View as School Admin</button>
                </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('admin.schools.edit', $school)); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Edit</a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school->is_active): ?>
                    <form method="POST" action="<?php echo e(route('admin.schools.deactivate', $school)); ?>" class="inline"><?php echo csrf_field(); ?><button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition">Deactivate</button></form>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('admin.schools.activate', $school)); ?>" class="inline"><?php echo csrf_field(); ?><button type="submit" class="px-4 py-2 border border-green-300 text-green-600 rounded-lg text-sm font-medium hover:bg-green-50 transition">Activate</button></form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500">Board</p><p class="text-sm font-medium"><?php echo e($school->board_affiliation); ?></p></div>
            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm"><?php echo e($school->email ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm"><?php echo e($school->phone ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">City</p><p class="text-sm"><?php echo e($school->city ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">State</p><p class="text-sm"><?php echo e($school->state ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Status</p><p class="text-sm font-medium <?php echo e($school->is_active ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($school->is_active ? 'Active' : 'Inactive'); ?></p></div>
            <div><p class="text-xs text-gray-500">Plan</p><p class="text-sm font-medium"><?php echo e(ucfirst($school->subscription_plan ?? 'trial')); ?></p></div>
            <div><p class="text-xs text-gray-500">Max Students</p><p class="text-sm"><?php echo e($school->max_students); ?></p></div>
            <div><p class="text-xs text-gray-500">Created</p><p class="text-sm"><?php echo e($school->created_at?->format('d M Y')); ?></p></div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($adminUser)): ?>
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-3">School Admin</h3>
        <div class="grid grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500">Name</p><p class="text-sm font-medium"><?php echo e($adminUser->name); ?></p></div>
            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm"><?php echo e($adminUser->email); ?></p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm"><?php echo e($adminUser->phone ?? '—'); ?></p></div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/super-admin/schools/show.blade.php ENDPATH**/ ?>