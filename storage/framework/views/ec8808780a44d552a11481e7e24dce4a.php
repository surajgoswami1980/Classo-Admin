

<?php $__env->startSection('title', 'Payroll'); ?>
<?php $__env->startSection('page-title', 'Payroll & Payslips'); ?>

<?php
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
?>

<?php $__env->startSection('content'); ?>
<div x-data="{
        showStructure:false, showPayslip:false,
        sForm:{ user_id:'', name:'', basic:0, hra:0, allowances:0, deductions:0 },
        pForm:{ user_id:'', name:'', month:<?php echo e($month); ?>, year:<?php echo e($year); ?>, lop_days:0, extra_deductions:0, remarks:'' },
        openStructure(s){ this.sForm={ user_id:s.user_id, name:s.name, basic:s.basic||0, hra:s.hra||0, allowances:s.allowances||0, deductions:s.deductions||0 }; this.showStructure=true; },
        openPayslip(s){ this.pForm={ user_id:s.user_id, name:s.name, month:<?php echo e($month); ?>, year:<?php echo e($year); ?>, lop_days:0, extra_deductions:0, remarks:'' }; this.showPayslip=true; },
        get sGross(){ return (+this.sForm.basic||0)+(+this.sForm.hra||0)+(+this.sForm.allowances||0); },
        get sNet(){ return this.sGross-(+this.sForm.deductions||0); },
     }" class="space-y-6">

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Payslips (<?php echo e($months[$month]); ?> <?php echo e($year); ?>)</p><p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($summary->payslips ?? 0); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Net</p><p class="text-2xl font-bold text-blue-600 mt-1">₹<?php echo e(number_format($summary->total_net ?? 0)); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Paid</p><p class="text-2xl font-bold text-green-600 mt-1">₹<?php echo e(number_format($summary->paid_net ?? 0)); ?></p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Pending</p><p class="text-2xl font-bold text-amber-600 mt-1">₹<?php echo e(number_format($summary->pending_net ?? 0)); ?></p></div>
    </div>

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <select name="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($m); ?>" <?php echo e($m == $month ? 'selected' : ''); ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <select name="year" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = now()->year; $y >= now()->year - 3; $y--): ?><option value="<?php echo e($y); ?>" <?php echo e($y == $year ? 'selected' : ''); ?>><?php echo e($y); ?></option><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">View</button>
        </form>
    </div>

    
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Staff & Salary Structures</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Staff</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Basic</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">HRA</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Allowances</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Deductions</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Net</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($s->name); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($s->designation ?? '—'); ?></p>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600"><?php echo e($s->basic !== null ? '₹'.number_format($s->basic) : '—'); ?></td>
                    <td class="px-4 py-3 text-right text-gray-600"><?php echo e($s->hra !== null ? '₹'.number_format($s->hra) : '—'); ?></td>
                    <td class="px-4 py-3 text-right text-gray-600"><?php echo e($s->allowances !== null ? '₹'.number_format($s->allowances) : '—'); ?></td>
                    <td class="px-4 py-3 text-right text-gray-600"><?php echo e($s->deductions !== null ? '₹'.number_format($s->deductions) : '—'); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900"><?php echo e($s->net !== null ? '₹'.number_format($s->net) : '—'); ?></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click='openStructure(<?php echo json_encode($s, 15, 512) ?>)' class="text-xs font-medium text-blue-600 hover:underline"><?php echo e($s->structure_id ? 'Edit Salary' : 'Set Salary'); ?></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->structure_id): ?>
                        <button @click='openPayslip(<?php echo json_encode($s, 15, 512) ?>)' class="text-xs font-medium text-green-600 hover:underline ml-3">Generate Payslip</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No active staff found. Add teachers or team members first.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Payslips — <?php echo e($months[$month]); ?> <?php echo e($year); ?></h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Staff</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Gross</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">LOP Days</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Deductions</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Net Pay</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $payslips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e($p->staff_name); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e($p->designation ?? '—'); ?></p>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">₹<?php echo e(number_format($p->gross)); ?></td>
                    <td class="px-4 py-3 text-center text-gray-600"><?php echo e($p->lop_days); ?></td>
                    <td class="px-4 py-3 text-right text-gray-600">₹<?php echo e(number_format($p->deductions)); ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">₹<?php echo e(number_format($p->net)); ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($p->status === 'paid' ? 'text-green-700 bg-green-50' : 'text-amber-700 bg-amber-50'); ?>"><?php echo e(ucfirst($p->status)); ?></span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="<?php echo e(panel_route('payroll.payslip.document', $p->id)); ?>" target="_blank" class="text-xs font-medium text-teal-600 hover:underline">Payslip</a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->status !== 'paid'): ?>
                        <form method="POST" action="<?php echo e(panel_route('payroll.payslip.paid', $p->id)); ?>" class="inline"><?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline ml-3">Mark Paid</button>
                        </form>
                        <?php else: ?>
                        <span class="text-xs text-gray-400 ml-3">Paid <?php echo e($p->paid_on ? \Illuminate\Support\Carbon::parse($p->paid_on)->format('d M') : ''); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No payslips generated for this period.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div x-show="showStructure" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showStructure=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Salary Structure</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="sForm.name"></p>
            <form method="POST" action="<?php echo e(panel_route('payroll.structure.store')); ?>" class="space-y-4"><?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" :value="sForm.user_id">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Basic *</label><input type="number" name="basic" x-model="sForm.basic" min="0" step="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">HRA</label><input type="number" name="hra" x-model="sForm.hra" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Allowances</label><input type="number" name="allowances" x-model="sForm.allowances" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Deductions</label><input type="number" name="deductions" x-model="sForm.deductions" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                    <span class="text-gray-500">Gross: <span class="font-semibold text-gray-800">₹<span x-text="sGross.toLocaleString()"></span></span></span>
                    <span class="text-gray-500">Net: <span class="font-semibold text-green-700">₹<span x-text="sNet.toLocaleString()"></span></span></span>
                </div>
                <div class="flex justify-end gap-2"><button type="button" @click="showStructure=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button></div>
            </form>
        </div>
    </div>

    
    <div x-show="showPayslip" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showPayslip=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Generate Payslip</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="pForm.name"></p>
            <form method="POST" action="<?php echo e(panel_route('payroll.payslip.generate')); ?>" class="space-y-4"><?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" :value="pForm.user_id">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Month *</label>
                        <select name="month" x-model="pForm.month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($m); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Year *</label>
                        <select name="year" x-model="pForm.year" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = now()->year; $y >= now()->year - 3; $y--): ?><option value="<?php echo e($y); ?>"><?php echo e($y); ?></option><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">LOP Days</label><input type="number" name="lop_days" x-model="pForm.lop_days" min="0" max="31" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Extra Deductions</label><input type="number" name="extra_deductions" x-model="pForm.extra_deductions" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Remarks</label><textarea name="remarks" x-model="pForm.remarks" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showPayslip=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Generate</button></div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/payroll/index.blade.php ENDPATH**/ ?>