<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Per-school split payout settings (opened from School Management → Payment Settings).
 *
 * All online payments are collected on the PLATFORM gateway set in the API .env
 * (PAYMENT_GATEWAY=razorpay|cashfree). A school only carries split data:
 *  - Razorpay Route linked account id (acc_...)
 *  - Cashfree Easy Split vendor id
 *  - optional platform fee % / route fee % overrides
 *
 * No split data → payments still go to the platform gateway and are flagged
 * for manual settlement. The NestJS API reads payment_settings per request.
 */
class PaymentSettingsController extends Controller
{
    public function edit(School $school)
    {
        $current = DB::table('payment_settings')->where('school_id', $school->id)->first();

        $stats = DB::table('payment_transactions')
            ->where('school_id', $school->id)
            ->where('status', 'success')
            ->where('gateway', '<>', 'offline')
            ->selectRaw('COUNT(*) as payments')
            ->selectRaw('COALESCE(SUM(amount),0) as collected')
            ->selectRaw('COALESCE(SUM(platform_commission),0) as platform_fee')
            ->selectRaw('COALESCE(SUM(gateway_fee),0) as gateway_fee')
            ->selectRaw("COALESCE(SUM(CASE WHEN split_status IN ('scheduled','processed') THEN school_share ELSE 0 END),0) as auto_settled")
            ->selectRaw("COALESCE(SUM(CASE WHEN split_status IN ('manual','failed') THEN school_share ELSE 0 END),0) as pending_manual")
            ->first();

        $gateway = \App\Support\PlatformSettings::get('payment', 'gateway') === 'cashfree' ? 'cashfree' : 'razorpay';
        $defaults = [
            'gateway' => $gateway,
            'platform_fee_percent' => (float) \App\Support\PlatformSettings::get('payment', 'platform_fee_percent'),
            'gateway_fee_percent' => (float) \App\Support\PlatformSettings::get('payment', 'gateway_fee_percent'),
            'split_product_on' => $gateway === 'cashfree'
                ? \App\Support\PlatformSettings::bool('cashfree', 'split_enabled')
                : \App\Support\PlatformSettings::bool('razorpay', 'route_enabled'),
        ];

        return view('admin.payment-settings.edit', compact('school', 'current', 'stats', 'defaults'));
    }

    public function update(Request $request, School $school)
    {
        $v = $request->validate([
            'split_enabled' => 'nullable|boolean',
            'razorpay_linked_account_id' => ['nullable', 'string', 'max:60', 'regex:/^acc_[A-Za-z0-9]+$/'],
            'cashfree_vendor_id' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
            'platform_fee_percent' => 'nullable|numeric|min:0|max:50',
            'gateway_fee_percent' => 'nullable|numeric|min:0|max:50',
        ], [
            'razorpay_linked_account_id.regex' => 'Razorpay linked account id must look like acc_XXXXXXXXXXXX.',
            'cashfree_vendor_id.regex' => 'Vendor id may only contain letters, numbers, - and _.',
        ]);

        $platformPct = $v['platform_fee_percent'] ?? null;
        $gatewayPct = $v['gateway_fee_percent'] ?? null;
        if ((float) $platformPct + (float) $gatewayPct >= 100) {
            return back()->withInput()->withErrors(['platform_fee_percent' => 'Platform fee + route fee must be below 100%.']);
        }

        $splitEnabled = $request->boolean('split_enabled');
        $linked = trim((string) ($v['razorpay_linked_account_id'] ?? '')) ?: null;
        $vendor = trim((string) ($v['cashfree_vendor_id'] ?? '')) ?: null;
        if ($splitEnabled && !$linked && !$vendor) {
            return back()->withInput()->withErrors(['split_enabled' => 'Add a Razorpay linked account id or a Cashfree vendor id to enable split.']);
        }

        $data = [
            'split_enabled' => $splitEnabled,
            'razorpay_linked_account_id' => $linked,
            'cashfree_vendor_id' => $vendor,
            'platform_fee_percent' => $platformPct,
            'gateway_fee_percent' => $gatewayPct,
            'updated_at' => now(),
        ];

        $exists = DB::table('payment_settings')->where('school_id', $school->id)->exists();
        if ($exists) {
            DB::table('payment_settings')->where('school_id', $school->id)->update($data);
        } else {
            DB::table('payment_settings')->insert($data + [
                'school_id' => $school->id,
                'active_gateway' => 'none',
                'created_at' => now(),
            ]);
        }

        return redirect()->route('admin.schools.payment-settings', $school)
            ->with('success', 'Payment settings saved for ' . $school->name . '.');
    }
}
