<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Creates / fetches / cancels payment links on the PLATFORM gateway.
 * No split is attached — link payments settle fully into the platform account.
 *
 * Razorpay: POST /v1/payment_links   (status: created | partially_paid | paid | expired | cancelled)
 * Cashfree: POST /pg/links           (status: ACTIVE | PARTIALLY_PAID | PAID | EXPIRED | CANCELLED)
 */
class PaymentLinkGateway
{
    public static function provider(): string
    {
        return PlatformSettings::get('payment', 'gateway') === 'cashfree' ? 'cashfree' : 'razorpay';
    }

    /**
     * @param array{reference:string, amount:float, description:string, name:string, email:?string, phone:string, expires_at:\Carbon\Carbon, notify_sms:bool, notify_email:bool, notes:array} $d
     * @return array{gateway:string, id:string, url:string, status:string}
     */
    public static function create(array $d): array
    {
        return self::provider() === 'cashfree' ? self::cashfreeCreate($d) : self::razorpayCreate($d);
    }

    /** @return array{status:string, amount_paid:float, payment_id:?string} */
    public static function fetch(string $gateway, string $linkId): array
    {
        return $gateway === 'cashfree' ? self::cashfreeFetch($linkId) : self::razorpayFetch($linkId);
    }

    public static function cancel(string $gateway, string $linkId): void
    {
        if ($gateway === 'cashfree') {
            $c = self::cashfreeClient();
            $resp = $c['http']->post("{$c['base']}/links/" . rawurlencode($linkId) . '/cancel');
        } else {
            $resp = self::razorpayClient()->post("https://api.razorpay.com/v1/payment_links/{$linkId}/cancel");
        }
        if (!$resp->successful()) {
            throw new RuntimeException(self::error($resp, 'Could not cancel the link.'));
        }
    }

    // ─── Razorpay ───────────────────────────────────────────────

    private static function razorpayClient()
    {
        $id = PlatformSettings::get('razorpay', 'key_id');
        $secret = PlatformSettings::get('razorpay', 'key_secret');
        if (!$id || !$secret) {
            throw new RuntimeException('Razorpay keys are not configured (Integrations → Payments).');
        }
        return Http::withBasicAuth($id, $secret)->acceptJson()->timeout(15);
    }

    private static function razorpayCreate(array $d): array
    {
        $payload = [
            'amount' => (int) round($d['amount'] * 100),
            'currency' => 'INR',
            'accept_partial' => false,
            'description' => mb_substr($d['description'], 0, 2048),
            'reference_id' => $d['reference'],
            'expire_by' => $d['expires_at']->getTimestamp(),
            'customer' => array_filter([
                'name' => $d['name'],
                'email' => $d['email'] ?: null,
                'contact' => self::phone($d['phone']),
            ]),
            'notify' => ['sms' => $d['notify_sms'], 'email' => $d['notify_email'] && $d['email']],
            'reminder_enable' => true,
            'notes' => array_map(fn ($v) => mb_substr((string) $v, 0, 250), array_slice($d['notes'], 0, 15, true)),
        ];
        $resp = self::razorpayClient()->post('https://api.razorpay.com/v1/payment_links', $payload);
        if (!$resp->successful() || !$resp->json('short_url')) {
            throw new RuntimeException(self::error($resp, 'Razorpay could not create the payment link.'));
        }
        return ['gateway' => 'razorpay', 'id' => $resp->json('id'), 'url' => $resp->json('short_url'), 'status' => self::normalise($resp->json('status'))];
    }

    private static function razorpayFetch(string $id): array
    {
        $resp = self::razorpayClient()->get("https://api.razorpay.com/v1/payment_links/{$id}");
        if (!$resp->successful()) {
            throw new RuntimeException(self::error($resp, 'Could not fetch the link from Razorpay.'));
        }
        $payments = $resp->json('payments') ?: [];
        $captured = collect($payments)->firstWhere('status', 'captured');
        return [
            'status' => self::normalise($resp->json('status')),
            'amount_paid' => ((int) $resp->json('amount_paid', 0)) / 100,
            'payment_id' => $captured['payment_id'] ?? null,
        ];
    }

    // ─── Cashfree ───────────────────────────────────────────────

    private static function cashfreeClient(): array
    {
        $id = PlatformSettings::get('cashfree', 'app_id');
        $secret = PlatformSettings::get('cashfree', 'secret_key');
        if (!$id || !$secret) {
            throw new RuntimeException('Cashfree keys are not configured (Integrations → Payments).');
        }
        $prod = PlatformSettings::get('cashfree', 'env') === 'PROD';
        return [
            'base' => $prod ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg',
            'http' => Http::withHeaders(['x-client-id' => $id, 'x-client-secret' => $secret, 'x-api-version' => '2023-08-01'])->acceptJson()->timeout(15),
        ];
    }

    private static function cashfreeCreate(array $d): array
    {
        $c = self::cashfreeClient();
        $payload = [
            'link_id' => $d['reference'],
            'link_amount' => round($d['amount'], 2),
            'link_currency' => 'INR',
            'link_purpose' => mb_substr($d['description'], 0, 500),
            'link_partial_payments' => false,
            'link_expiry_time' => $d['expires_at']->toIso8601String(),
            'customer_details' => array_filter([
                'customer_name' => $d['name'],
                'customer_email' => $d['email'] ?: null,
                'customer_phone' => substr(preg_replace('/\D/', '', $d['phone']), -10),
            ]),
            'link_notify' => ['send_sms' => $d['notify_sms'], 'send_email' => $d['notify_email'] && $d['email']],
            'link_notes' => array_map(fn ($v) => mb_substr((string) $v, 0, 250), array_slice($d['notes'], 0, 5, true)),
        ];
        $resp = $c['http']->post("{$c['base']}/links", $payload);
        if (!$resp->successful() || !$resp->json('link_url')) {
            throw new RuntimeException(self::error($resp, 'Cashfree could not create the payment link.'));
        }
        return ['gateway' => 'cashfree', 'id' => $resp->json('link_id') ?: $d['reference'], 'url' => $resp->json('link_url'), 'status' => self::normalise($resp->json('link_status'))];
    }

    private static function cashfreeFetch(string $id): array
    {
        $c = self::cashfreeClient();
        $resp = $c['http']->get("{$c['base']}/links/" . rawurlencode($id));
        if (!$resp->successful()) {
            throw new RuntimeException(self::error($resp, 'Could not fetch the link from Cashfree.'));
        }
        return [
            'status' => self::normalise($resp->json('link_status')),
            'amount_paid' => (float) $resp->json('link_amount_paid', 0),
            'payment_id' => null,
        ];
    }

    // ─── Helpers ────────────────────────────────────────────────

    /** Map gateway statuses to ours. */
    public static function normalise(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid' => 'paid',
            'partially_paid' => 'partially_paid',
            'expired' => 'expired',
            'cancelled' => 'cancelled',
            default => 'created', // created | active | issued
        };
    }

    /** E.164-ish: default to +91 for 10-digit Indian numbers. */
    private static function phone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        return '+' . ltrim($digits, '+');
    }

    private static function error($resp, string $fallback): string
    {
        $msg = $resp->json('error.description') ?? $resp->json('message') ?? null;
        return $msg ? "{$fallback} ({$msg})" : "{$fallback} (HTTP {$resp->status()})";
    }
}
