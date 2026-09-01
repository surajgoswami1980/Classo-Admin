

<?php $__env->startSection('title', 'Teacher Details'); ?>
<?php $__env->startSection('page-title', 'Teacher Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Teacher Details</h2>
            <p class="text-sm text-gray-500">Viewing teacher profile information</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(panel_route('teachers.edit', $teacher->id)); ?>" class="px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                Edit Teacher
            </a>
            <a href="<?php echo e(panel_route('teachers.index')); ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ← Back to List
            </a>
        </div>
    </div>

    <!-- Teacher Profile Card -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-white text-xl font-bold">
                    <?php echo e(strtoupper(substr($teacher->name ?? '', 0, 2))); ?>

                </div>
                <div>
                    <h3 class="text-xl font-bold text-white"><?php echo e($teacher->name ?? ''); ?></h3>
                    <p class="text-purple-100 text-sm"><?php echo e($teacher->designation ?? ''); ?> · <?php echo e(ucfirst(str_replace('_', ' ', $teacher->department ?? ''))); ?></p>
                </div>
                <div class="ml-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($teacher->status ?? '') == 'active'): ?>
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
                    <?php elseif(($teacher->status ?? '') == 'on_leave'): ?>
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">On Leave</span>
                    <?php else: ?>
                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-700"><?php echo e(ucfirst($teacher->status ?? 'N/A')); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Personal Info -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Personal Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->email ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->phone ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date of Birth</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->dob ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Gender</p>
                        <p class="text-sm text-gray-900"><?php echo e(ucfirst($teacher->gender ?? 'N/A')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Qualification</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->qualification ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Professional Info -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Professional Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Employee ID</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->employee_id ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Designation</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->designation ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Department</p>
                        <p class="text-sm text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $teacher->department ?? 'N/A'))); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Joining Date</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->joining_date ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Experience</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->experience ?? '0'); ?> years</p>
                    </div>
                </div>
            </div>

            <!-- Address Info -->
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Address</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->address ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">City</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->city ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">State</p>
                        <p class="text-sm text-gray-900"><?php echo e($teacher->state ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/teachers/show.blade.php ENDPATH**/ ?>