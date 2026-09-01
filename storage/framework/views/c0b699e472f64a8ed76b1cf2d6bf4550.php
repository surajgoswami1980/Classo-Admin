
<?php $__env->startSection('title', 'Attendance Report'); ?>
<?php $__env->startSection('page-title', 'Attendance Report'); ?>
<?php $__env->startSection('content'); ?>
<div>
    <form method="GET" class="bg-white rounded-xl border p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="student" <?php echo e(($filters['type'] ?? 'student') === 'student' ? 'selected' : ''); ?>>Student</option>
                    <option value="staff" <?php echo e(($filters['type'] ?? '') === 'staff' ? 'selected' : ''); ?>>Staff</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Classes</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($id); ?>" <?php echo e(($filters['class_id'] ?? '') == $id ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Generate</button>
            </div>
        </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->isNotEmpty()): ?>
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Present</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Absent</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Late/Leave</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">%</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($row->name); ?> <span class="text-xs text-gray-400"><?php echo e($row->roll_number ?? $row->employee_id ?? ''); ?></span></td>
                    <td class="px-4 py-3 text-center text-green-600 font-medium"><?php echo e($row->present_days); ?></td>
                    <td class="px-4 py-3 text-center text-red-600 font-medium"><?php echo e($row->absent_days); ?></td>
                    <td class="px-4 py-3 text-center text-yellow-600"><?php echo e($row->late_days ?? $row->leave_days ?? 0); ?></td>
                    <td class="px-4 py-3 text-center text-gray-600"><?php echo e($row->total_days); ?></td>
                    <td class="px-4 py-3 text-center font-bold <?php echo e($row->total_days > 0 && ($row->present_days / $row->total_days * 100) < 75 ? 'text-red-600' : 'text-green-600'); ?>">
                        <?php echo e($row->total_days > 0 ? round(($row->present_days / $row->total_days) * 100, 1) : 0); ?>%
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php elseif(!empty($filters['from_date'])): ?>
    <div class="bg-white rounded-xl border p-8 text-center text-gray-400">No attendance data found for the selected period</div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/attendance/report.blade.php ENDPATH**/ ?>