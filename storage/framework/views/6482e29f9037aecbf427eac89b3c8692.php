

<?php $__env->startSection('title', 'Onboard New School'); ?>
<?php $__env->startSection('page-title', 'Onboard New School'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl">
    <form method="POST" action="<?php echo e(route('admin.schools.store')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- School Info -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">School Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name *</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Delhi Public School">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Code * <span class="text-xs text-gray-400">(unique)</span></label>
                    <input type="text" name="code" value="<?php echo e(old('code')); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono uppercase focus:ring-2 focus:ring-blue-500 outline-none" placeholder="DPSRN01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Board Affiliation *</label>
                    <select name="board_affiliation" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="CBSE">CBSE</option>
                        <option value="ICSE">ICSE</option>
                        <option value="STATE">State Board</option>
                        <option value="UNIVERSITY">University</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo e(old('contact_email')); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?php echo e(old('contact_phone')); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"><?php echo e(old('address')); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="<?php echo e(old('city')); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <input type="text" name="state" value="<?php echo e(old('state')); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Subscription -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Subscription</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                    <select name="subscription_status" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="trial">Trial (30 days)</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Students</label>
                    <input type="number" name="max_students" value="300" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Admin Credentials -->
        <div class="bg-white rounded-xl border p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">School Admin Account</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Name *</label>
                    <input type="text" name="admin_name" value="<?php echo e(old('admin_name')); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email *</label>
                    <input type="email" name="admin_email" value="<?php echo e(old('admin_email')); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Phone</label>
                    <input type="text" name="admin_phone" value="<?php echo e(old('admin_phone')); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" name="admin_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Min 8 characters">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('admin.schools.index')); ?>" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Onboard School</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/super-admin/schools/create.blade.php ENDPATH**/ ?>