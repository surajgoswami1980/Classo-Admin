
<?php $__env->startSection('title', 'Student Details'); ?>
<?php $__env->startSection('page-title', 'Student Details'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <a href="<?php echo e(panel_route('students.index')); ?>" class="text-sm text-gray-500 hover:text-blue-600">← Back to list</a>
        <a href="<?php echo e(panel_route('students.edit', $student->id)); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Edit Student</a>
    </div>

    <div class="bg-white rounded-xl border p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                <?php echo e(strtoupper(substr($student->user_name ?? 'S', 0, 1))); ?>

            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900"><?php echo e($student->user_name ?? '—'); ?></h2>
                <p class="text-sm text-gray-500"><?php echo e($student->class_name ?? ''); ?> - <?php echo e($student->section_name ?? ''); ?> | Roll: <?php echo e($student->roll_number ?? '—'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500">Admission No</p><p class="text-sm font-medium"><?php echo e($student->admission_number ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Roll Number</p><p class="text-sm font-medium"><?php echo e($student->roll_number ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm"><?php echo e($student->email ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm"><?php echo e($student->phone ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Gender</p><p class="text-sm"><?php echo e(ucfirst($student->gender ?? '—')); ?></p></div>
            <div><p class="text-xs text-gray-500">DOB</p><p class="text-sm"><?php echo e($student->date_of_birth ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Blood Group</p><p class="text-sm"><?php echo e($student->blood_group ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Status</p><p class="text-sm font-medium <?php echo e($student->status === 'active' ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e(ucfirst($student->status ?? '')); ?></p></div>
            <div><p class="text-xs text-gray-500">Admission Date</p><p class="text-sm"><?php echo e($student->admission_date ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Father</p><p class="text-sm"><?php echo e($student->father_name ?? '—'); ?> <?php echo e($student->father_phone ? '('.$student->father_phone.')' : ''); ?></p></div>
            <div><p class="text-xs text-gray-500">Mother</p><p class="text-sm"><?php echo e($student->mother_name ?? '—'); ?></p></div>
            <div><p class="text-xs text-gray-500">Address</p><p class="text-sm"><?php echo e($student->address ?? '—'); ?>, <?php echo e($student->city ?? ''); ?> <?php echo e($student->state ?? ''); ?></p></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\students\show.blade.php ENDPATH**/ ?>