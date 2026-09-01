

<?php $__env->startSection('title', 'Students'); ?>
<?php $__env->startSection('page-title', 'Students'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Student Management</h2>
            <p class="text-sm text-gray-500"><?php echo e($students->total() ?? 0); ?> students total</p>
        </div>
        <a href="<?php echo e(panel_route('students.create')); ?>" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Student
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl border p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search name, phone, admission no..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All Status</option>
                <option value="active" <?php echo e(($filters['status'] ?? '') === 'active' ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e(($filters['status'] ?? '') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                <option value="transferred" <?php echo e(($filters['status'] ?? '') === 'transferred' ? 'selected' : ''); ?>>Transferred</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filter</button>
            <a href="<?php echo e(panel_route('students.index')); ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium text-center hover:bg-gray-50">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Class</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Section</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Roll No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Admission No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Phone</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($student->user_name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($student->class_name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($student->section_name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($student->roll_number ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($student->admission_number ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($student->phone ?? '—'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->status === 'active'): ?>
                                <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">Active</span>
                            <?php elseif($student->status === 'inactive'): ?>
                                <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">Inactive</span>
                            <?php else: ?>
                                <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e(ucfirst($student->status ?? '')); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="<?php echo e(panel_route('students.show', $student->id)); ?>" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </a>
                                <a href="<?php echo e(panel_route('students.edit', $student->id)); ?>" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <p class="font-medium">No students found</p>
                            <a href="<?php echo e(panel_route('students.create')); ?>" class="text-blue-600 text-sm mt-1 inline-block">+ Add first student</a>
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->hasPages()): ?>
        <div class="px-4 py-3 border-t"><?php echo e($students->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/students/index.blade.php ENDPATH**/ ?>