
<?php
    $memberDirectIds = isset($member) ? $member->directPolicies->pluck('id')->all() : [];
    $assignmentType = isset($member) && $member->access_role_id ? 'role' : 'direct';
?>
<div x-data="{ assignmentType: '<?php echo e($assignmentType); ?>' }" class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
            <input type="text" name="name" required maxlength="255" value="<?php echo e($member->name ?? ''); ?>"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
            <input type="email" name="email" required maxlength="255" value="<?php echo e($member->email ?? ''); ?>"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
            <input type="text" name="phone" maxlength="15" value="<?php echo e($member->phone ?? ''); ?>"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($member)): ?>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Panel Role</label>
            <select name="panel_role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="sub-admin">Sub Admin</option>
                <option value="incharge">Class Incharge</option>
            </select>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Access</label>
        <div class="flex gap-4 mb-3">
            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                <input type="radio" name="assignment_type" value="role" x-model="assignmentType" class="text-blue-600 focus:ring-blue-500">
                Use a Role
            </label>
            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                <input type="radio" name="assignment_type" value="direct" x-model="assignmentType" class="text-blue-600 focus:ring-blue-500">
                Assign Policies Directly
            </label>
        </div>

        <div x-show="assignmentType === 'role'">
            <select name="access_role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Select a role...</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($role->id); ?>" <?php echo e((isset($member) && $member->access_role_id === $role->id) ? 'selected' : ''); ?>><?php echo e($role->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>

        <div x-show="assignmentType === 'direct'" class="border rounded-lg divide-y max-h-48 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <label class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700">
                <input type="checkbox" name="policy_ids[]" value="<?php echo e($policy->id); ?>"
                    <?php echo e(in_array($policy->id, $memberDirectIds) ? 'checked' : ''); ?>

                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <?php echo e($policy->name); ?>

            </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="px-3 py-4 text-sm text-gray-400">No policies yet — create one first in the Policies tab.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/team/partials/user-form.blade.php ENDPATH**/ ?>