<?php
    $title = $isReceipt ? 'Fee Receipt' : 'Fee Invoice';
    $amount = (float) $invoice->amount;
    $lateFee = (float) ($invoice->late_fee ?? 0);
    $total = (float) ($invoice->total_amount ?? ($amount + $lateFee));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title); ?> — <?php echo e($invoice->invoice_number); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1f2937; background: #f3f4f6; padding: 24px; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
        .head { display: flex; justify-content: space-between; align-items: flex-start; padding: 28px 32px; background: linear-gradient(135deg,#1d4ed8,#2563eb); color: #fff; }
        .head h1 { font-size: 22px; font-weight: 800; }
        .head .code { opacity: .85; font-size: 13px; margin-top: 2px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.due { background: #fef3c7; color: #92400e; }
        .meta { display: flex; justify-content: space-between; padding: 24px 32px; gap: 24px; border-bottom: 1px solid #e5e7eb; }
        .meta .block { font-size: 13px; line-height: 1.7; }
        .meta .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        .meta .val { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; padding: 12px 32px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        td { padding: 14px 32px; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        td.r, th.r { text-align: right; }
        .totals { padding: 16px 32px; }
        .totals .row { display: flex; justify-content: flex-end; gap: 40px; font-size: 14px; padding: 4px 0; }
        .totals .row.grand { font-size: 18px; font-weight: 800; color: #111827; border-top: 2px solid #e5e7eb; margin-top: 8px; padding-top: 12px; }
        .foot { padding: 20px 32px 28px; font-size: 12px; color: #6b7280; display: flex; justify-content: space-between; align-items: flex-end; }
        .stamp { border: 2px solid #16a34a; color: #16a34a; padding: 6px 14px; border-radius: 8px; font-weight: 800; transform: rotate(-6deg); font-size: 15px; }
        .actions { max-width: 800px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none; } .sheet { box-shadow: none; border-radius: 0; } }
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
                <div class="code">
                    <?php echo e($school->address ?? ''); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school?->city): ?>, <?php echo e($school->city); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($school?->phone): ?> · <?php echo e($school->phone); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:16px;font-weight:700"><?php echo e(strtoupper($title)); ?></div>
                <div class="code"><?php echo e($invoice->invoice_number); ?></div>
                <div style="margin-top:8px">
                    <span class="badge <?php echo e($isReceipt ? 'paid' : 'due'); ?>"><?php echo e($isReceipt ? 'PAID' : strtoupper($invoice->status)); ?></span>
                </div>
            </div>
        </div>

        <div class="meta">
            <div class="block">
                <div class="label">Billed To</div>
                <div class="val"><?php echo e($invoice->student_name); ?></div>
                <div>Class <?php echo e($invoice->class_name ?? '—'); ?><?php echo e($invoice->section_name ? ' - ' . $invoice->section_name : ''); ?></div>
                <div>Roll: <?php echo e($invoice->roll_number ?? '—'); ?> · Adm: <?php echo e($invoice->admission_number ?? '—'); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->father_name): ?><div>Guardian: <?php echo e($invoice->father_name); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->student_phone): ?><div>Phone: <?php echo e($invoice->student_phone); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="block" style="text-align:right">
                <div class="label"><?php echo e($isReceipt ? 'Payment Date' : 'Issue Date'); ?></div>
                <div class="val"><?php echo e(\Illuminate\Support\Carbon::parse($invoice->paid_date ?? $invoice->created_at)->format('d M Y')); ?></div>
                <div class="label" style="margin-top:10px">Due Date</div>
                <div class="val"><?php echo e(\Illuminate\Support\Carbon::parse($invoice->due_date)->format('d M Y')); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isReceipt && $payment): ?>
                    <div class="label" style="margin-top:10px">Payment Mode</div>
                    <div class="val"><?php echo e(ucfirst($payment->payment_method ?? $payment->gateway ?? 'Online')); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->razorpay_payment_id): ?><div style="font-size:11px;color:#6b7280">Txn: <?php echo e($payment->razorpay_payment_id); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Description</th><th class="r">Amount</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo e($invoice->fee_name ?? 'Tuition Fee'); ?> — Installment <?php echo e($invoice->fee_installment_id ?? 1); ?></td>
                    <td class="r">₹<?php echo e(number_format($amount, 2)); ?></td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lateFee > 0): ?>
                <tr>
                    <td>Late Fee</td>
                    <td class="r">₹<?php echo e(number_format($lateFee, 2)); ?></td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>Subtotal</span><span>₹<?php echo e(number_format($amount, 2)); ?></span></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lateFee > 0): ?><div class="row"><span>Late Fee</span><span>₹<?php echo e(number_format($lateFee, 2)); ?></span></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="row grand"><span><?php echo e($isReceipt ? 'Amount Paid' : 'Total Due'); ?></span><span>₹<?php echo e(number_format($total, 2)); ?></span></div>
        </div>

        <div class="foot">
            <div>
                <p>This is a computer-generated <?php echo e(strtolower($title)); ?>.</p>
                <p>Generated on <?php echo e(now()->format('d M Y, h:i A')); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isReceipt): ?><div class="stamp">PAID</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/admin/fees/document.blade.php ENDPATH**/ ?>