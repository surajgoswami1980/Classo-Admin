<?php $__env->startSection('title', 'Manage Resources & Permissions'); ?>
<?php $__env->startSection('page-title', 'Manage Resources & Permissions'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6" x-data="{ showCreateResource: false, editingResource: null, addingActionTo: null, editingAction: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Resources & Permissions</h2>
            <p class="text-sm text-gray-500">The catalog every school's Policies are built from. Most resources/actions are auto-derived from app routes — you can rename them, toggle availability, or add custom ones here.</p>
        </div>
        <button @click="showCreateResource = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Resource
        </button>
    </div>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $resources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50">
                <template x-if="editingResource !== <?php echo e($resource->id); ?>">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-semibold text-gray-900"><?php echo e($resource->name); ?></h3>
                        <span class="font-mono text-xs text-gray-400"><?php echo e($resource->slug); ?></span>
                    </div>
                </template>
                <template x-if="editingResource === <?php echo e($resource->id); ?>">
                    <form method="POST" action="<?php echo e(route('admin.resources.update', $resource)); ?>" class="flex items-center gap-2">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <input type="text" name="name" value="<?php echo e($resource->name); ?>" class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                        <button type="submit" class="text-xs font-medium text-blue-600 hover:underline">Save</button>
                        <button type="button" @click="editingResource = null" class="text-xs text-gray-400 hover:underline">Cancel</button>
                    </form>
                </template>
                <div class="flex items-center gap-2">
                    <form method="POST" action="<?php echo e(route('admin.resources.status', $resource)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($resource->status ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100'); ?>">
                            <?php echo e($resource->status ? 'Active' : 'Inactive'); ?>

                        </button>
                    </form>
                    <button @click="editingResource = editingResource === <?php echo e($resource->id); ?> ? null : <?php echo e($resource->id); ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                    </button>
                    <button @click="addingActionTo = addingActionTo === <?php echo e($resource->id); ?> ? null : <?php echo e($resource->id); ?>" class="text-xs font-medium text-blue-600 hover:underline">+ Action</button>
                </div>
            </div>

            <div x-show="addingActionTo === <?php echo e($resource->id); ?>" x-cloak class="px-5 py-3 bg-blue-50/50 border-b">
                <form method="POST" action="<?php echo e(route('admin.resources.actions.store', $resource)); ?>" class="flex items-end gap-2">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Action key</label>
                        <input type="text" name="action" placeholder="e.g. export" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Label</label>
                        <input type="text" name="label" placeholder="e.g. Export" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                </form>
            </div>

            <div class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $resource->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                <div class="flex items-center justify-between px-5 py-2.5">
                    <template x-if="editingAction !== <?php echo e($action->id); ?>">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-700"><?php echo e($action->label); ?></span>
                            <span class="font-mono text-xs text-gray-400"><?php echo e($resource->slug); ?>.<?php echo e($action->action); ?></span>
                        </div>
                    </template>
                    <template x-if="editingAction === <?php echo e($action->id); ?>">
                        <form method="POST" action="<?php echo e(route('admin.resources.actions.update', $action)); ?>" class="flex items-center gap-2">
                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <input type="text" name="label" value="<?php echo e($action->label); ?>" class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            <button type="submit" class="text-xs font-medium text-blue-600 hover:underline">Save</button>
                            <button type="button" @click="editingAction = null" class="text-xs text-gray-400 hover:underline">Cancel</button>
                        </form>
                    </template>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="<?php echo e(route('admin.resources.actions.status', $action)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($action->status ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100'); ?>">
                                <?php echo e($action->status ? 'Active' : 'Inactive'); ?>

                            </button>
                        </form>
                        <button @click="editingAction = editingAction === <?php echo e($action->id); ?> ? null : <?php echo e($action->id); ?>" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                <p class="px-5 py-4 text-sm text-gray-400">No actions defined yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
            No resources yet. Run the seeder or add one manually.
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Create resource modal -->
    <div x-show="showCreateResource" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreateResource = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Resource</h3>
            <form method="POST" action="<?php echo e(route('admin.resources.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Display Name</label>
                    <input type="text" name="name" required maxlength="100" placeholder="e.g. Hostel" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Slug (used in permission keys)</label>
                    <input type="text" name="slug" required maxlength="100" placeholder="e.g. hostel" pattern="[a-zA-Z0-9_-]+" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCreateResource = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/access-control/index.blade.php ENDPATH**/ ?>