
<?php
    $checkedPolicyIds = isset($role) ? $role->policies->pluck('id')->all() : [];
?>
<div class="space-y-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Role Name</label>
        <input type="text" name="name" required maxlength="100" value="<?php echo e($role->name ?? ''); ?>"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Policies granted to this role</label>
        <div class="border rounded-lg divide-y max-h-64 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <label class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700">
                <input type="checkbox" name="policy_ids[]" value="<?php echo e($policy->id); ?>"
                    <?php echo e(in_array($policy->id, $checkedPolicyIds) ? 'checked' : ''); ?>

                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <?php echo e($policy->name); ?>

            </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="px-3 py-4 text-sm text-gray-400">No policies yet — create one first in the Policies tab.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/team/partials/role-form.blade.php ENDPATH**/ ?>