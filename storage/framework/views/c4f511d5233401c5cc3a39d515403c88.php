
<?php
    $checkedIds = isset($policy) ? $policy->resourcePermissions->pluck('id')->all() : [];
?>
<div class="space-y-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Policy Name</label>
        <input type="text" name="name" required maxlength="150" value="<?php echo e($policy->name ?? ''); ?>"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"><?php echo e($policy->description ?? ''); ?></textarea>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Permissions</label>
        <div class="border rounded-lg divide-y max-h-80 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resource->actions->isNotEmpty()): ?>
                <div class="p-3">
                    <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2"><?php echo e($resource->name); ?></p>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $resource->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $action->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                                <input type="checkbox" name="resource_permission_ids[]" value="<?php echo e($permission->id); ?>"
                                    <?php echo e(in_array($permission->id, $checkedIds) ? 'checked' : ''); ?>

                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <?php echo e($action->label); ?>

                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/team/partials/policy-form.blade.php ENDPATH**/ ?>