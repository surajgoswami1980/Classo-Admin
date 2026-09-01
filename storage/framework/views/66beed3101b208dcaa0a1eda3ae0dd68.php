
<?php $__env->startSection('title', 'Sections'); ?>
<?php $__env->startSection('page-title', 'Sections'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl">
    <!-- Add Section Form -->
    <div class="bg-white rounded-xl border p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Add New Section</h3>
        <form method="POST" action="<?php echo e(panel_route('academic.sections.store')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="md:col-span-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700"><?php echo e($errors->first()); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Section Name *</label>
                <input type="text" name="name" required placeholder="e.g. A, B, C" maxlength="10" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class *</label>
                <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Class</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Capacity</label>
                <input type="number" name="capacity" value="40" min="1" max="200" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Add Section</button>
            </div>
        </form>
    </div>

    <!-- Sections List -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Section</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Class</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Capacity</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($section->name); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($section->class_name); ?></td>
                    <td class="px-4 py-3 text-center text-gray-500"><?php echo e($section->capacity); ?></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="<?php echo e(panel_route('academic.sections.destroy', $section->id)); ?>" class="inline" onsubmit="return confirm('Delete this section?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No sections created. Add classes first.</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/academic/sections.blade.php ENDPATH**/ ?>