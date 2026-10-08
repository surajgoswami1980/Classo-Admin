<?php
    $basic = (float) $payslip->basic;
    $hra = (float) ($payslip->hra ?? 0);
    $allowances = (float) ($payslip->allowances ?? 0);
    $gross = (float) ($payslip->gross ?? ($basic + $hra + $allowances));
    $deductions = (float) ($payslip->deductions ?? 0);
    $net = (float) ($payslip->net ?? ($gross - $deductions));
    $earnings = [
        ['Basic Salary', $basic],
        ['House Rent Allowance (HRA)', $hra],
        ['Other Allowances', $allowances],
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip — <?php echo e($payslip->staff_name); ?> — <?php echo e($monthName); ?> <?php echo e($payslip->year); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1f2937; background: #f3f4f6; padding: 24px; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
        .head { padding: 28px 32px; background: linear-gradient(135deg,#0f766e,#0d9488); color: #fff; display:flex; justify-content:space-between; align-items:flex-start; }
        .head h1 { font-size: 22px; font-weight: 800; }
        .head .sub { opacity:.85; font-size:13px; margin-top:2px; }
        .period { text-align:right; }
        .period .m { font-size:16px; font-weight:700; }
        .meta { display:flex; justify-content:space-between; gap:24px; padding:24px 32px; border-bottom:1px solid #e5e7eb; font-size:13px; line-height:1.7; }
        .label { color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
        .val { font-weight:600; }
        .cols { display:flex; gap:0; }
        .col { flex:1; }
        .col h3 { font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:12px 32px; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
        .col.earn h3 { color:#166534; }
        .col.ded h3 { color:#991b1b; }
        .line { display:flex; justify-content:space-between; padding:12px 32px; font-size:14px; border-bottom:1px solid #f3f4f6; }
        .subtotal { display:flex; justify-content:space-between; padding:14px 32px; font-weight:700; background:#f9fafb; }
        .net { padding:20px 32px; background:#ecfdf5; display:flex; justify-content:space-between; align-items:center; }
        .net .amt { font-size:24px; font-weight:800; color:#065f46; }
        .net .words { font-size:12px; color:#6b7280; }
        .status { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700; }
        .status.paid { background:#dcfce7; color:#166534; }
        .status.gen { background:#fef3c7; color:#92400e; }
        .foot { padding:20px 32px 28px; font-size:12px; color:#6b7280; }
        .actions { max-width:800px; margin:0 auto 16px; display:flex; justify-content:flex-end; gap:10px; }
        .btn { padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; text-decoration:none; display:inline-block; }
        .btn-print { background:#0d9488; color:#fff; }
        .btn-back { background:#fff; color:#374151; border:1px solid #d1d5db; }
        @media print { body { background:#fff; padding:0; } .actions { display:none; } .sheet { box-shadow:none; border-radius:0; } }
    </style>
</head>
<body>
    <div class="actions">
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-back">← Back</a>
        <button onclick="window.print()" class="btn btn-print">🖨 Print / Save as PDF</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div>
                <h1><?php echo e($school->name ?? 'School'); ?></h1>
                <div class="sub"><?php echo e($school->address ?? ''); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school?->city): ?>, <?php echo e($school->city); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div class="sub">Payslip</div>
            </div>
            <div class="period">
                <div class="m"><?php echo e($monthName); ?> <?php echo e($payslip->year); ?></div>
                <div style="margin-top:8px">
                    <span class="status <?php echo e($payslip->status === 'paid' ? 'paid' : 'gen'); ?>"><?php echo e(strtoupper($payslip->status)); ?></span>
                </div>
            </div>
        </div>

        <div class="meta">
            <div>
                <div class="label">Employee</div>
                <div class="val"><?php echo e($payslip->staff_name); ?></div>
                <div><?php echo e($payslip->designation ?? '—'); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payslip->department): ?> · <?php echo e($payslip->department); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payslip->employee_id): ?><div>Emp ID: <?php echo e($payslip->employee_id); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div style="text-align:right">
                <div class="label">Pay Period</div>
                <div class="val"><?php echo e($monthName); ?> <?php echo e($payslip->year); ?></div>
                <div class="label" style="margin-top:10px">LOP Days</div>
                <div class="val"><?php echo e($payslip->lop_days ?? 0); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payslip->status === 'paid' && $payslip->paid_on): ?>
                    <div class="label" style="margin-top:10px">Paid On</div>
                    <div class="val"><?php echo e(\Illuminate\Support\Carbon::parse($payslip->paid_on)->format('d M Y')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="cols">
            <div class="col earn">
                <h3>Earnings</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $earnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="line"><span><?php echo e($label); ?></span><span>₹<?php echo e(number_format($value, 2)); ?></span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="subtotal"><span>Gross Earnings</span><span>₹<?php echo e(number_format($gross, 2)); ?></span></div>
            </div>
            <div class="col ded" style="border-left:1px solid #e5e7eb">
                <h3>Deductions</h3>
                <div class="line"><span>Total Deductions</span><span>₹<?php echo e(number_format($deductions, 2)); ?></span></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($payslip->lop_days ?? 0) > 0): ?>
                    <div class="line" style="color:#6b7280;font-size:12px"><span>(incl. loss of pay for <?php echo e($payslip->lop_days); ?> day/s)</span><span></span></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="subtotal"><span>Total Deductions</span><span>₹<?php echo e(number_format($deductions, 2)); ?></span></div>
            </div>
        </div>

        <div class="net">
            <div>
                <div class="label">Net Pay</div>
                <div class="words">Gross ₹<?php echo e(number_format($gross, 2)); ?> − Deductions ₹<?php echo e(number_format($deductions, 2)); ?></div>
            </div>
            <div class="amt">₹<?php echo e(number_format($net, 2)); ?></div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payslip->remarks): ?>
        <div class="foot"><strong>Remarks:</strong> <?php echo e($payslip->remarks); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="foot">
            <p>This is a computer-generated payslip and does not require a signature.</p>
            <p>Generated on <?php echo e(now()->format('d M Y, h:i A')); ?></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/payroll/document.blade.php ENDPATH**/ ?>