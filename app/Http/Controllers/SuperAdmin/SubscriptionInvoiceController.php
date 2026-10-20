<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Platform → School subscription billing.
 *
 * Super-admin raises a subscription invoice for a school. The school-admin
 * sees "Pay Subscription" and pays via the PLATFORM's Razorpay account
 * (global keys in the admin .env), so the money settles to the platform —
 * NOT the school's own gateway.
 */
class SubscriptionInvoiceController extends Controller
{
    // ─── Super-admin side ────────────────────────────────────────────────

    public function index()
    {
        $invoices = DB::table('subscription_invoices as si')
            ->leftJoin('schools as s', 's.id', '=', 'si.school_id')
            ->select(['si.*', 's.name as school_name', 's.code as school_code'])
            ->orderByDesc('si.created_at')
            ->paginate(20);

        $schools = School::orderBy('name')->get(['id', 'name', 'code', 'subscription_plan']);

        $stats = [
            'total' => (float) DB::table('subscription_invoices')->where('status', 'paid')->sum('amount'),
            'unpaid' => (int) DB::table('subscription_invoices')->where('status', 'unpaid')->count(),
        ];

        return view('super-admin.subscription-invoices.index', compact('invoices', 'schools', 'stats'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'plan' => 'nullable|string|max:40',
            'amount' => 'required|numeric|min:1',
            'period' => 'nullable|string|max:40',
            'notes' => 'nullable|string|max:1000',
        ]);

        $number = 'SUB-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

        DB::table('subscription_invoices')->insert([
            'school_id' => $v['school_id'],
            'invoice_number' => $number,
            'plan' => $v['plan'] ?? null,
            'amount' => $v['amount'],
            'period' => $v['period'] ?? null,
            'notes' => $v['notes'] ?? null,
            'status' => 'unpaid',
            'gateway' => 'razorpay',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Subscription invoice {$number} raised.");
    }

    public function cancel($id)
    {
        DB::table('subscription_invoices')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);

        return back()->with('success', 'Invoice cancelled.');
    }

    // ─── School-admin side ───────────────────────────────────────────────

    public function mySubscription()
    {
        $schoolId = current_school_id() ?? auth()->user()?->school_id;

        $invoices = DB::table('subscription_invoices')
            ->where('school_id', $schoolId)
            ->orderByDesc('created_at')
            ->get();

        $razorpayKey = \App\Support\PlatformSettings::get('razorpay', 'key_id');

        return view('admin.subscription.index', compact('invoices', 'razorpayKey'));
    }

    /** Create a platform Razorpay order for an unpaid subscription invoice. */
    public function initiatePay(Request $request, $id)
    {
        $schoolId = current_school_id() ?? auth()->user()?->school_id;
        $invoice = DB::table('subscription_invoices')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($invoice, 404);
        if ($invoice->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Already paid.'], 422);
        }

        $keyId = \App\Support\PlatformSettings::get('razorpay', 'key_id');
        $keySecret = \App\Support\PlatformSettings::get('razorpay', 'key_secret');
        if (!$keyId || !$keySecret) {
            return response()->json(['success' => false, 'message' => 'Platform Razorpay keys not configured (Admin → Integrations).'], 422);
        }

        // Create a Razorpay order via REST (no SDK dependency in Laravel).
        $resp = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/orders', [
            'amount' => (int) round($invoice->amount * 100),
            'currency' => 'INR',
            'receipt' => $invoice->invoice_number,
            'notes' => ['school_id' => (string) $schoolId, 'type' => 'subscription'],
        ]);

        if (!$resp->successful()) {
            return response()->json(['success' => false, 'message' => 'Could not create payment order.'], 422);
        }

        $order = $resp->json();
        DB::table('subscription_invoices')->where('id', $id)->update(['razorpay_order_id' => $order['id'], 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'order_id' => $order['id'],
            'amount' => $invoice->amount,
            'key' => $keyId,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    /** Verify the platform Razorpay payment and mark the invoice paid. */
    public function verifyPay(Request $request, $id)
    {
        $schoolId = current_school_id() ?? auth()->user()?->school_id;
        $invoice = DB::table('subscription_invoices')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($invoice, 404);

        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $expected = hash_hmac(
            'sha256',
            $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'],
            (string) \App\Support\PlatformSettings::get('razorpay', 'key_secret')
        );

        if (!hash_equals($expected, $data['razorpay_signature'])) {
            return response()->json(['success' => false, 'message' => 'Verification failed.'], 422);
        }

        DB::table('subscription_invoices')->where('id', $id)->update([
            'status' => 'paid',
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'paid_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Subscription paid. Thank you!']);
    }
}
