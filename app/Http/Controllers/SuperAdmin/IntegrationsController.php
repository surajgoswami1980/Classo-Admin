<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Support\PlatformSettings;
use App\Support\SettingsCrypto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Super admin → Integrations: one place for every global credential.
 * Payment gateways (Razorpay Route / Cashfree Easy Split), Firebase push,
 * email and SMS. Values are stored in platform_settings (secrets encrypted)
 * and read by both this admin and the NestJS API.
 */
class IntegrationsController extends Controller
{
    private const TABS = ['payments', 'firebase', 'email', 'sms'];

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'payments';
        $defs = PlatformSettings::definitions();

        // Current values for the form (secrets are masked, never sent to the browser).
        $values = [];
        $sources = [];
        foreach ($defs as $group => $fields) {
            foreach ($fields as $key => $def) {
                $v = PlatformSettings::get($group, $key);
                $values[$group][$key] = str_starts_with($def['type'], 'secret') ? PlatformSettings::mask($v) : $v;
                $sources[$group][$key] = PlatformSettings::source($group, $key);
            }
        }

        $status = [];
        foreach (['razorpay', 'cashfree', 'firebase', 'email', 'sms'] as $g) {
            $status[$g] = PlatformSettings::status($g);
        }

        $splitStats = DB::table('payment_settings')
            ->selectRaw('SUM(split_enabled = 1 AND razorpay_linked_account_id IS NOT NULL) as razorpay_linked')
            ->selectRaw('SUM(split_enabled = 1 AND cashfree_vendor_id IS NOT NULL) as cashfree_linked')
            ->first();
        $totalSchools = DB::table('schools')->count();

        $firebaseProject = null;
        if ($json = PlatformSettings::get('firebase', 'service_account_json')) {
            $firebaseProject = json_decode($json, true)['project_id'] ?? null;
        }

        $apiBase = rtrim((string) config('services.nestjs.base_url'), '/');
        $webhooks = [
            'razorpay' => preg_replace('#/api$#', '', $apiBase) . '/fee/payment/webhook',
            'cashfree' => preg_replace('#/api$#', '', $apiBase) . '/fee/payment/webhook/cashfree',
        ];

        return view('super-admin.integrations.index', [
            'tab' => $tab,
            'defs' => $defs,
            'values' => $values,
            'sources' => $sources,
            'status' => $status,
            'splitStats' => $splitStats,
            'totalSchools' => $totalSchools,
            'firebaseProject' => $firebaseProject,
            'webhooks' => $webhooks,
            'cryptoReady' => SettingsCrypto::configured(),
        ]);
    }

    public function update(Request $request, string $section)
    {
        abort_unless(in_array($section, self::TABS, true), 404);
        if (!SettingsCrypto::configured()) {
            return back()->with('error', 'SETTINGS_ENCRYPTION_KEY is not set. Add it to both .env files before saving credentials.');
        }

        $groups = $section === 'payments' ? ['payment', 'razorpay', 'cashfree'] : [$section];
        $rules = [];
        foreach ($groups as $group) {
            foreach (PlatformSettings::definitions()[$group] as $key => $def) {
                $field = "{$group}.{$key}";
                $rules[$field] = match ($def['type']) {
                    'number' => ['nullable', 'numeric', 'min:0', 'max:' . ($key === 'smtp_port' ? 65535 : 50)],
                    'select' => ['nullable', 'in:' . implode(',', array_keys($def['options']))],
                    'toggle' => ['nullable', 'boolean'],
                    'secret_textarea' => ['nullable', 'string', 'max:20000'],
                    default => ['nullable', 'string', 'max:500'],
                };
            }
        }
        $rules['razorpay.key_id'][] = 'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/';
        $rules['email.from_address'][] = 'email';
        $rules['sms.api_url'][] = 'url';
        $rules['sms.api_url'][] = 'starts_with:https://,http://';
        $data = $request->validate(array_filter($rules, fn ($r, $f) => in_array(explode('.', $f)[0], $groups, true), ARRAY_FILTER_USE_BOTH), [
            'razorpay.key_id.regex' => 'Razorpay Key ID should look like rzp_test_… or rzp_live_….',
        ]);

        if ($section === 'firebase' && !empty($data['firebase']['service_account_json'])) {
            $sa = json_decode($data['firebase']['service_account_json'], true);
            if (!is_array($sa) || ($sa['type'] ?? null) !== 'service_account' || empty($sa['private_key']) || empty($sa['client_email'])) {
                return back()->withInput()->withErrors(['firebase.service_account_json' => 'That is not a valid Firebase service account JSON file.']);
            }
        }
        if ($section === 'payments') {
            $pf = (float) ($data['payment']['platform_fee_percent'] ?? 0);
            $gf = (float) ($data['payment']['gateway_fee_percent'] ?? 0);
            if ($pf + $gf >= 100) {
                return back()->withInput()->withErrors(['payment.platform_fee_percent' => 'Platform fee + route fee must be below 100%.']);
            }
        }

        foreach ($groups as $group) {
            $input = $request->input($group, []);
            foreach (PlatformSettings::definitions()[$group] as $key => $def) {
                if (str_starts_with($def['type'], 'secret') && $request->boolean("{$group}.{$key}__clear")) {
                    $input[$key . '__clear'] = true;
                }
            }
            PlatformSettings::save($group, $input, auth()->id());
        }

        return redirect()->route('admin.integrations.index', ['tab' => $section])
            ->with('success', ucfirst($section) . ' settings saved. The API picks them up within a minute.');
    }

    /** Test a connection with the SAVED credentials. Returns JSON for the UI. */
    public function test(Request $request, string $target)
    {
        try {
            return match ($target) {
                'razorpay' => $this->testRazorpay(),
                'cashfree' => $this->testCashfree(),
                'firebase' => $this->testFirebase(),
                'email' => $this->testEmail($request),
                'sms' => $this->testSms($request),
                default => abort(404),
            };
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function testRazorpay()
    {
        $id = PlatformSettings::get('razorpay', 'key_id');
        $secret = PlatformSettings::get('razorpay', 'key_secret');
        if (!$id || !$secret) {
            return response()->json(['ok' => false, 'message' => 'Save the Key ID and Key Secret first.'], 422);
        }
        $resp = Http::withBasicAuth($id, $secret)->timeout(10)->get('https://api.razorpay.com/v1/payments', ['count' => 1]);
        if ($resp->status() === 401) {
            return response()->json(['ok' => false, 'message' => 'Razorpay rejected the keys (401). Check Key ID / Secret and mode.'], 422);
        }
        if (!$resp->successful()) {
            return response()->json(['ok' => false, 'message' => 'Razorpay responded with HTTP ' . $resp->status() . '.'], 422);
        }
        $mode = str_starts_with($id, 'rzp_live_') ? 'live' : 'test';
        return response()->json(['ok' => true, 'message' => "Connected to Razorpay ({$mode} mode)."]);
    }

    private function testCashfree()
    {
        $id = PlatformSettings::get('cashfree', 'app_id');
        $secret = PlatformSettings::get('cashfree', 'secret_key');
        if (!$id || !$secret) {
            return response()->json(['ok' => false, 'message' => 'Save the App ID and Secret Key first.'], 422);
        }
        $prod = PlatformSettings::get('cashfree', 'env') === 'PROD';
        $base = $prod ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
        // A lookup for a non-existent order: 404 = keys OK, 401 = bad keys.
        $resp = Http::withHeaders(['x-client-id' => $id, 'x-client-secret' => $secret, 'x-api-version' => '2023-08-01'])
            ->timeout(10)->get("{$base}/orders/connection-test-" . time());
        if (in_array($resp->status(), [401, 403], true)) {
            return response()->json(['ok' => false, 'message' => 'Cashfree rejected the keys. Check App ID / Secret and environment.'], 422);
        }
        if ($resp->status() === 404 || $resp->successful()) {
            return response()->json(['ok' => true, 'message' => 'Connected to Cashfree (' . ($prod ? 'production' : 'sandbox') . ').']);
        }
        return response()->json(['ok' => false, 'message' => 'Cashfree responded with HTTP ' . $resp->status() . '.'], 422);
    }

    private function testFirebase()
    {
        $json = PlatformSettings::get('firebase', 'service_account_json');
        $sa = $json ? json_decode($json, true) : null;
        if (!is_array($sa) || empty($sa['private_key']) || empty($sa['client_email'])) {
            return response()->json(['ok' => false, 'message' => 'Save a service account JSON first.'], 422);
        }

        // Sign a JWT with the service-account key and exchange it for an access token.
        $now = time();
        $b64 = fn ($d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        $header = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $b64(json_encode([
            'iss' => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 600,
        ]));
        $sig = '';
        if (!openssl_sign("{$header}.{$claims}", $sig, $sa['private_key'], 'sha256WithRSAEncryption')) {
            return response()->json(['ok' => false, 'message' => 'The private key in the service account could not be read.'], 422);
        }
        $resp = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => "{$header}.{$claims}." . $b64($sig),
        ]);
        if (!$resp->successful() || !$resp->json('access_token')) {
            return response()->json(['ok' => false, 'message' => 'Google rejected the service account: ' . ($resp->json('error_description') ?? 'HTTP ' . $resp->status())], 422);
        }
        return response()->json(['ok' => true, 'message' => 'Firebase credentials are valid for project ' . ($sa['project_id'] ?? '(unknown)') . '.']);
    }

    private function testEmail(Request $request)
    {
        $to = $request->validate(['to' => 'required|email'])['to'];
        if (PlatformSettings::get('email', 'driver') === 'log') {
            return response()->json(['ok' => false, 'message' => 'Provider is "Log only". Choose SMTP or Amazon SES and save first.'], 422);
        }
        PlatformSettings::applyMailConfig();
        app('mail.manager')->forgetMailers();
        Mail::html(
            '<p>This is a test email from the <strong>' . e(config('app.name')) . '</strong> Integrations page.</p><p>Your email settings are working.</p>',
            fn ($m) => $m->to($to)->subject('Test email — email settings are working'),
        );
        return response()->json(['ok' => true, 'message' => "Test email sent to {$to}. Check the inbox (and spam)."]);
    }

    private function testSms(Request $request)
    {
        $to = $request->validate(['to' => ['required', 'regex:/^\+?[0-9 \-]{8,16}$/']])['to'];
        $url = PlatformSettings::get('sms', 'api_url');
        if (PlatformSettings::get('sms', 'provider') !== 'http' || !$url) {
            return response()->json(['ok' => false, 'message' => 'Choose "HTTP gateway", add the API URL and save first.'], 422);
        }
        $digits = preg_replace('/\D/', '', $to);
        $final = str_replace(
            ['{phone}', '{message}', '{key}', '{sender}'],
            [rawurlencode($digits), rawurlencode('Test SMS from ' . config('app.name') . ' — your SMS gateway is working.'), rawurlencode((string) PlatformSettings::get('sms', 'api_key')), rawurlencode((string) PlatformSettings::get('sms', 'sender_id'))],
            $url,
        );
        $resp = Http::timeout(10)->get($final);
        if (!$resp->successful()) {
            return response()->json(['ok' => false, 'message' => 'SMS gateway responded with HTTP ' . $resp->status() . ': ' . mb_strimwidth(strip_tags($resp->body()), 0, 160, '…')], 422);
        }
        return response()->json(['ok' => true, 'message' => 'Gateway accepted the request (HTTP ' . $resp->status() . '). Check the phone ' . $to . '.']);
    }
}
