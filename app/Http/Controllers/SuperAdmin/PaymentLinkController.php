<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\School;
use App\Support\PaymentLinkGateway;
use App\Support\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Super admin → Payment Links.
 * Generate a link (onboarding / setup / subscription), share it on
 * WhatsApp / SMS / email, and track whether it has been paid.
 * Money from links always settles in the platform account (no split).
 */
class PaymentLinkController extends Controller
{
    public const PURPOSES = [
        'onboarding' => 'Onboarding fee',
        'setup' => 'Setup & data migration',
        'subscription' => 'Subscription',
        'other' => 'Other',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $links = DB::table('payment_links as pl')
            ->leftJoin('schools as s', 's.id', '=', 'pl.school_id')
            ->select('pl.*', 's.name as school_name')
            ->when($status, fn ($w) => $w->where('pl.status', $status))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('pl.customer_name', 'like', "%{$q}%")
                ->orWhere('pl.customer_phone', 'like', "%{$q}%")
                ->orWhere('pl.customer_email', 'like', "%{$q}%")
                ->orWhere('pl.reference', 'like', "%{$q}%")
                ->orWhere('s.name', 'like', "%{$q}%")))
            ->orderByDesc('pl.created_at')
            ->paginate(20)
            ->withQueryString();

        // Expire links whose time has passed (gateway expires them too).
        DB::table('payment_links')->where('status', 'created')->where('expires_at', '<', now())->update(['status' => 'expired', 'updated_at' => now()]);

        $stats = DB::table('payment_links')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_paid END),0) as collected")
            ->selectRaw("SUM(status = 'paid') as paid_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'created' THEN amount END),0) as pending_amount")
            ->selectRaw("SUM(status = 'created') as pending_count")
            ->selectRaw('COUNT(*) as total')
            ->first();

        $prefill = $this->prefill($request);
        $schools = School::orderBy('name')->get(['id', 'name', 'code', 'email', 'phone', 'principal_name']);

        return view('super-admin.payment-links.index', [
            'links' => $links,
            'stats' => $stats,
            'status' => $status,
            'q' => $q,
            'schools' => $schools,
            'prefill' => $prefill,
            'purposes' => self::PURPOSES,
            'gateway' => PaymentLinkGateway::provider(),
            'gatewayReady' => PlatformSettings::status(PaymentLinkGateway::provider()) === 'connected',
        ]);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'purpose' => 'required|in:' . implode(',', array_keys(self::PURPOSES)),
            'amount' => 'required|numeric|min:1|max:10000000',
            'description' => 'required|string|max:255',
            'school_id' => 'nullable|exists:schools,id',
            'enquiry_id' => 'nullable|exists:enquiries,id',
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => ['required', 'regex:/^\+?[0-9 \-]{10,16}$/'],
            'expires_in_days' => 'required|integer|min:1|max:90',
            'notify_sms' => 'nullable|boolean',
            'notify_email' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ], ['customer_phone.regex' => 'Enter a valid mobile number (10 digits, optional +91).']);

        $reference = 'PL-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        $expiresAt = now()->addDays((int) $v['expires_in_days'])->endOfDay();

        try {
            $link = PaymentLinkGateway::create([
                'reference' => $reference,
                'amount' => (float) $v['amount'],
                'description' => $v['description'],
                'name' => $v['customer_name'],
                'email' => $v['customer_email'] ?? null,
                'phone' => $v['customer_phone'],
                'expires_at' => $expiresAt,
                'notify_sms' => $request->boolean('notify_sms'),
                'notify_email' => $request->boolean('notify_email'),
                'notes' => array_filter([
                    'type' => 'payment_link',
                    'purpose' => $v['purpose'],
                    'reference' => $reference,
                    'school_id' => $v['school_id'] ?? null,
                    'enquiry_id' => $v['enquiry_id'] ?? null,
                ]),
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $id = DB::table('payment_links')->insertGetId([
            'reference' => $reference,
            'purpose' => $v['purpose'],
            'description' => $v['description'],
            'amount' => $v['amount'],
            'school_id' => $v['school_id'] ?? null,
            'enquiry_id' => $v['enquiry_id'] ?? null,
            'customer_name' => $v['customer_name'],
            'customer_email' => $v['customer_email'] ?? null,
            'customer_phone' => $v['customer_phone'],
            'gateway' => $link['gateway'],
            'gateway_link_id' => $link['id'],
            'short_url' => $link['url'],
            'status' => $link['status'],
            'expires_at' => $expiresAt,
            'notified_sms' => $request->boolean('notify_sms'),
            'notified_email' => $request->boolean('notify_email'),
            'notes' => $v['notes'] ?? null,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!empty($v['enquiry_id'])) {
            Enquiry::whereKey($v['enquiry_id'])->where('status', 'new')->update(['status' => 'contacted']);
        }

        return redirect()->route('admin.payment-links.index', ['created' => $id])
            ->with('success', "Payment link {$reference} created. Copy it or share it on WhatsApp.");
    }

    /** Pull the latest status from the gateway (fallback when a webhook was missed). */
    public function refresh($id)
    {
        $link = DB::table('payment_links')->where('id', $id)->first();
        abort_unless($link, 404);
        try {
            $remote = PaymentLinkGateway::fetch($link->gateway, $link->gateway_link_id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        $this->applyStatus($link, $remote['status'], $remote['amount_paid'], $remote['payment_id']);
        return back()->with('success', "Status for {$link->reference}: " . str_replace('_', ' ', $remote['status']) . '.');
    }

    public function cancel($id)
    {
        $link = DB::table('payment_links')->where('id', $id)->first();
        abort_unless($link, 404);
        if ($link->status !== 'created') {
            return back()->with('error', 'Only unpaid, active links can be cancelled.');
        }
        try {
            PaymentLinkGateway::cancel($link->gateway, $link->gateway_link_id);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        DB::table('payment_links')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);
        return back()->with('success', "Link {$link->reference} cancelled.");
    }

    private function applyStatus(object $link, string $status, float $amountPaid, ?string $paymentId): void
    {
        DB::table('payment_links')->where('id', $link->id)->update(array_filter([
            'status' => $status,
            'amount_paid' => $amountPaid,
            'gateway_payment_id' => $paymentId ?: $link->gateway_payment_id,
            'paid_at' => $status === 'paid' ? ($link->paid_at ?? now()) : null,
            'updated_at' => now(),
        ], fn ($v) => $v !== null));

        if ($status === 'paid' && $link->enquiry_id) {
            Enquiry::whereKey($link->enquiry_id)->update(['status' => 'converted']);
        }
    }

    /** Prefill the create form from ?school= or ?enquiry= . */
    private function prefill(Request $request): array
    {
        if ($sid = $request->integer('school')) {
            $s = School::find($sid);
            if ($s) {
                return [
                    'school_id' => $s->id,
                    'customer_name' => $s->principal_name ?: $s->name,
                    'customer_email' => $s->principal_email ?: $s->email,
                    'customer_phone' => $s->principal_phone ?: $s->phone,
                    'description' => "Onboarding fee — {$s->name}",
                    'open' => true,
                ];
            }
        }
        if ($eid = $request->integer('enquiry')) {
            $e = Enquiry::find($eid);
            if ($e) {
                return [
                    'enquiry_id' => $e->id,
                    'customer_name' => $e->name,
                    'customer_email' => $e->email,
                    'customer_phone' => $e->phone,
                    'description' => 'Onboarding fee' . ($e->organization ? " — {$e->organization}" : ''),
                    'open' => true,
                ];
            }
        }
        return ['open' => false];
    }
}
