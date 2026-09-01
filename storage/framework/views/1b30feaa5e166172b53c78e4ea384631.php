
<?php $__env->startSection('title', 'My Profile'); ?>
<?php $__env->startSection('page-title', 'My Profile'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl space-y-6">

    <!-- Profile Info -->
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-700">
                <?php echo e(strtoupper(substr($user->name ?? 'U', 0, 1))); ?>

            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                <p class="text-sm text-gray-500"><?php echo e($user->roles->first()?->name ?? 'User'); ?> · <?php echo e($user->school?->name ?? 'Platform'); ?></p>
            </div>
        </div>

        <form method="POST" action="<?php echo e(panel_route('profile.update')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any() && !$errors->has('current_password')): ?>
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input type="text" value="<?php echo e($user->roles->first()?->name ?? '—'); ?>" disabled class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Change Password</h3>
        <form method="POST" action="<?php echo e(panel_route('profile.password')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->has('current_password')): ?>
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700"><?php echo e($errors->first('current_password')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">Change Password</button>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Account Info</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">User ID:</span> <span class="font-medium"><?php echo e($user->id); ?></span></div>
            <div><span class="text-gray-500">School ID:</span> <span class="font-medium"><?php echo e($user->school_id ?? '—'); ?></span></div>
            <div><span class="text-gray-500">Last Login:</span> <span class="font-medium"><?php echo e($user->last_login_at?->format('d M Y, h:i A') ?? '—'); ?></span></div>
            <div><span class="text-gray-500">Joined:</span> <span class="font-medium"><?php echo e($user->created_at?->format('d M Y') ?? '—'); ?></span></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/profile/index.blade.php ENDPATH**/ ?>