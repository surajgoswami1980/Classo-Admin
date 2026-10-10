

<?php $__env->startSection('title', 'Report Card'); ?>
<?php $__env->startSection('page-title', 'Report Card'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Student Report Card</h2>
            <p class="text-sm text-gray-500">Detailed academic performance report</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                Print
            </button>
            <a href="<?php echo e(panel_route('exams.index')); ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ← Back to Exams
            </a>
        </div>
    </div>

    <!-- Report Card -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden print:border-0 print:shadow-none">
        <!-- School Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6 text-center print:bg-white print:text-black">
            <h1 class="text-xl font-bold text-white print:text-gray-900"><?php echo e(config('app.school_name', 'School Name')); ?></h1>
            <p class="text-blue-100 text-sm mt-1 print:text-gray-600">Academic Report Card</p>
        </div>

        <!-- Student Info -->
        <div class="px-8 py-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Student Name</p>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($student->first_name ?? ''); ?> <?php echo e($student->last_name ?? ''); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Class & Section</p>
                    <p class="text-sm font-semibold text-gray-900">Class <?php echo e($student->class ?? '-'); ?> - <?php echo e($student->section ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Roll No</p>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($student->roll_no ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Admission No</p>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($student->admission_no ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Exam</p>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($exam->name ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Father's Name</p>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($student->father_name ?? '-'); ?></p>
                </div>
            </div>
        </div>

        <!-- Marks Table -->
        <div class="px-8 py-6">
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-b">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase border-b">Subject</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border-b">Max Marks</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border-b">Marks Obtained</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border-b">Grade</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border-b">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $results ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($index + 1); ?></td>
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($result->subject ?? ''); ?></td>
                            <td class="px-4 py-3 text-center text-gray-700"><?php echo e($result->max_marks ?? 100); ?></td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-900"><?php echo e($result->obtained_marks ?? 0); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    <?php if(in_array($result->grade ?? '', ['A+', 'A'])): ?> bg-green-100 text-green-700
                                    <?php elseif(in_array($result->grade ?? '', ['B+', 'B'])): ?> bg-blue-100 text-blue-700
                                    <?php elseif(($result->grade ?? '') == 'C'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-red-100 text-red-700
                                    <?php endif; ?>
                                "><?php echo e($result->grade ?? '-'); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs"><?php echo e($result->remarks ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                <p class="text-sm">No results available</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($results)): ?>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total</td>
                            <td class="px-4 py-3 text-center"><?php echo e($summary['total_max'] ?? 0); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e($summary['total_obtained'] ?? 0); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e($summary['overall_grade'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center"><?php echo e($summary['percentage'] ?? 0); ?>%</td>
                        </tr>
                    </tfoot>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </table>
        </div>

        <!-- Result Summary -->
        <div class="px-8 pb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Overall Percentage</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo e($summary['percentage'] ?? 0); ?>%</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Overall Grade</p>
                    <p class="text-lg font-bold text-gray-900"><?php echo e($summary['overall_grade'] ?? '-'); ?></p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Result</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($summary['result'] ?? '') == 'pass'): ?>
                        <p class="text-lg font-bold text-green-600">PASS</p>
                    <?php else: ?>
                        <p class="text-lg font-bold text-red-600"><?php echo e(strtoupper($summary['result'] ?? 'N/A')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views\admin\exams\report-card.blade.php ENDPATH**/ ?>