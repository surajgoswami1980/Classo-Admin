<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Global platform credentials (Integrations page).
 *
 * Lookup order for every key: value saved by the super admin in
 * platform_settings → legacy .env variable → built-in default.
 * So existing .env setups keep working until values are saved in the panel.
 */
class PlatformSettings
{
    private const CACHE_KEY = 'platform_settings.all';

    /**
     * Field definitions per group.
     * type: text | secret | select | number | toggle | textarea
     */
    public static function definitions(): array
    {
        return [
            'payment' => [
                'gateway' => ['label' => 'Default gateway', 'type' => 'select', 'options' => ['razorpay' => 'Razorpay', 'cashfree' => 'Cashfree'], 'env' => 'PAYMENT_GATEWAY', 'default' => 'razorpay'],
                'platform_fee_percent' => ['label' => 'Platform fee (%)', 'type' => 'number', 'env' => 'PLATFORM_FEE_PERCENT', 'default' => '1.5', 'hint' => 'Kept by the platform on every online payment.'],
                'gateway_fee_percent' => ['label' => 'Route / split fee (%)', 'type' => 'number', 'env' => 'GATEWAY_FEE_PERCENT', 'default' => '2', 'hint' => 'Recovers the gateway charge. Deducted from the school share.'],
            ],
            'razorpay' => [
                'mode' => ['label' => 'Mode', 'type' => 'select', 'options' => ['test' => 'Test', 'live' => 'Live'], 'default' => 'test'],
                'key_id' => ['label' => 'Key ID', 'type' => 'text', 'env' => 'RAZORPAY_KEY_ID', 'placeholder' => 'rzp_live_xxxxxxxxxxxx'],
                'key_secret' => ['label' => 'Key Secret', 'type' => 'secret', 'env' => 'RAZORPAY_KEY_SECRET'],
                'webhook_secret' => ['label' => 'Webhook Secret', 'type' => 'secret', 'env' => 'RAZORPAY_WEBHOOK_SECRET', 'hint' => 'Razorpay Dashboard → Webhooks. Unsigned webhooks are rejected.'],
                'route_enabled' => ['label' => 'Route (split payouts)', 'type' => 'toggle', 'default' => '1', 'hint' => 'Send each school its share automatically via Razorpay Route.'],
            ],
            'cashfree' => [
                'env' => ['label' => 'Environment', 'type' => 'select', 'options' => ['TEST' => 'Sandbox', 'PROD' => 'Production'], 'env' => 'CASHFREE_ENV', 'default' => 'TEST'],
                'app_id' => ['label' => 'App ID', 'type' => 'text', 'env' => 'CASHFREE_APP_ID'],
                'secret_key' => ['label' => 'Secret Key', 'type' => 'secret', 'env' => 'CASHFREE_SECRET_KEY', 'hint' => 'Also used to verify Cashfree webhooks.'],
                'split_enabled' => ['label' => 'Easy Split (split payouts)', 'type' => 'toggle', 'default' => '1', 'hint' => 'Send each school its share automatically via Cashfree Easy Split.'],
            ],
            'firebase' => [
                'enabled' => ['label' => 'Push notifications', 'type' => 'toggle', 'default' => '1'],
                'service_account_json' => ['label' => 'Service account JSON', 'type' => 'secret_textarea', 'env' => 'FIREBASE_SERVICE_ACCOUNT_JSON', 'hint' => 'Firebase Console → Project settings → Service accounts → Generate new private key.'],
            ],
            'email' => [
                'driver' => ['label' => 'Provider', 'type' => 'select', 'options' => ['log' => 'Log only (dev)', 'smtp' => 'SMTP', 'ses' => 'Amazon SES'], 'default' => 'log'],
                'from_address' => ['label' => 'From email', 'type' => 'text', 'env' => 'SES_FROM_EMAIL', 'placeholder' => 'no-reply@yourdomain.com'],
                'from_name' => ['label' => 'From name', 'type' => 'text', 'default' => 'MyClasses'],
                'smtp_host' => ['label' => 'SMTP host', 'type' => 'text', 'placeholder' => 'smtp.gmail.com', 'show_when' => 'smtp'],
                'smtp_port' => ['label' => 'SMTP port', 'type' => 'number', 'default' => '587', 'show_when' => 'smtp'],
                'smtp_encryption' => ['label' => 'Encryption', 'type' => 'select', 'options' => ['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'], 'default' => 'tls', 'show_when' => 'smtp'],
                'smtp_username' => ['label' => 'SMTP username', 'type' => 'text', 'show_when' => 'smtp'],
                'smtp_password' => ['label' => 'SMTP password', 'type' => 'secret', 'show_when' => 'smtp'],
                'ses_region' => ['label' => 'AWS region', 'type' => 'text', 'env' => 'AWS_REGION', 'default' => 'ap-south-1', 'show_when' => 'ses'],
                'ses_access_key' => ['label' => 'AWS access key ID', 'type' => 'text', 'env' => 'AWS_ACCESS_KEY_ID', 'show_when' => 'ses'],
                'ses_secret_key' => ['label' => 'AWS secret access key', 'type' => 'secret', 'env' => 'AWS_SECRET_ACCESS_KEY', 'show_when' => 'ses'],
            ],
            'sms' => [
                'provider' => ['label' => 'Provider', 'type' => 'select', 'options' => ['log' => 'Log only (dev)', 'http' => 'HTTP gateway (MSG91, Fast2SMS, TextLocal…)'], 'env' => 'SMS_PROVIDER', 'default' => 'log'],
                'api_url' => ['label' => 'API URL template', 'type' => 'text', 'env' => 'SMS_API_URL', 'placeholder' => 'https://api.provider.com/send?to={phone}&msg={message}&key={key}&sender={sender}', 'hint' => 'Placeholders: {phone} {message} {key} {sender}', 'show_when' => 'http'],
                'api_key' => ['label' => 'API key', 'type' => 'secret', 'env' => 'SMS_API_KEY', 'show_when' => 'http'],
                'sender_id' => ['label' => 'Sender ID', 'type' => 'text', 'env' => 'SMS_SENDER_ID', 'placeholder' => 'SCHERP', 'show_when' => 'http'],
            ],
        ];
    }

    /** All stored values keyed "group.key" (decrypted), cached briefly. */
    private static function stored(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, function () {
            if (!Schema::hasTable('platform_settings')) {
                return [];
            }
            $out = [];
            foreach (DB::table('platform_settings')->get() as $row) {
                $value = $row->value;
                if ($row->is_secret) {
                    $value = SettingsCrypto::configured() ? SettingsCrypto::decrypt($value) : null;
                }
                $out[$row->group_name . '.' . $row->setting_key] = $value;
            }
            return $out;
        });
    }

    public static function get(string $group, string $key, $fallback = null)
    {
        $stored = self::stored();
        $full = $group . '.' . $key;
        if (array_key_exists($full, $stored) && $stored[$full] !== null && $stored[$full] !== '') {
            return $stored[$full];
        }
        $def = self::definitions()[$group][$key] ?? [];
        if (!empty($def['env'])) {
            $env = env($def['env']);
            if ($env !== null && $env !== '') {
                return $env;
            }
        }
        return $def['default'] ?? $fallback;
    }

    /** Where a value currently comes from: panel | env | default | none */
    public static function source(string $group, string $key): string
    {
        $stored = self::stored();
        $full = $group . '.' . $key;
        if (isset($stored[$full]) && $stored[$full] !== '') {
            return 'panel';
        }
        $def = self::definitions()[$group][$key] ?? [];
        if (!empty($def['env']) && env($def['env']) !== null && env($def['env']) !== '') {
            return 'env';
        }
        return isset($def['default']) ? 'default' : 'none';
    }

    public static function bool(string $group, string $key): bool
    {
        return filter_var(self::get($group, $key, '0'), FILTER_VALIDATE_BOOLEAN);
    }

    /** Save submitted values for one group. Blank secrets keep the existing value. */
    public static function save(string $group, array $input, ?int $userId = null): void
    {
        $defs = self::definitions()[$group] ?? [];
        foreach ($defs as $key => $def) {
            $isSecret = str_starts_with($def['type'], 'secret');
            $value = $input[$key] ?? null;

            if ($def['type'] === 'toggle') {
                $value = !empty($value) ? '1' : '0';
            }
            if ($isSecret) {
                if (!empty($input[$key . '__clear'])) {
                    DB::table('platform_settings')->where(['group_name' => $group, 'setting_key' => $key])->delete();
                    continue;
                }
                if ($value === null || $value === '') {
                    continue; // keep existing secret
                }
                $value = SettingsCrypto::encrypt((string) $value);
            } else {
                $value = $value === null ? null : trim((string) $value);
            }

            DB::table('platform_settings')->updateOrInsert(
                ['group_name' => $group, 'setting_key' => $key],
                ['value' => $value, 'is_secret' => $isSecret, 'updated_by' => $userId, 'updated_at' => now(), 'created_at' => now()],
            );
        }
        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** "rzp_live_A1B2…" style hint for a secret, never the full value. */
    public static function mask(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $len = strlen($value);
        return $len <= 8 ? str_repeat('•', 8) : substr($value, 0, 4) . str_repeat('•', 8) . substr($value, -4);
    }

    /** Is a group ready to use? */
    public static function status(string $group): string
    {
        return match ($group) {
            'razorpay' => self::get('razorpay', 'key_id') && self::get('razorpay', 'key_secret') ? 'connected' : 'not_configured',
            'cashfree' => self::get('cashfree', 'app_id') && self::get('cashfree', 'secret_key') ? 'connected' : 'not_configured',
            'firebase' => !self::bool('firebase', 'enabled') ? 'disabled' : (self::get('firebase', 'service_account_json') ? 'connected' : 'not_configured'),
            'email' => match (self::get('email', 'driver')) {
                'smtp' => self::get('email', 'smtp_host') && self::get('email', 'from_address') ? 'connected' : 'not_configured',
                'ses' => self::get('email', 'ses_access_key') && self::get('email', 'ses_secret_key') && self::get('email', 'from_address') ? 'connected' : 'not_configured',
                default => 'log',
            },
            'sms' => self::get('sms', 'provider') === 'http' && self::get('sms', 'api_url') ? 'connected' : 'log',
            default => 'connected',
        };
    }

    /** AWS-documented conversion of an IAM secret access key into an SES SMTP password. */
    public static function sesSmtpPassword(string $secretKey, string $region): string
    {
        $sig = hash_hmac('sha256', '11111111', 'AWS4' . $secretKey, true);
        foreach ([$region, 'ses', 'aws4_request', 'SendRawEmail'] as $part) {
            $sig = hash_hmac('sha256', $part, $sig, true);
        }
        return base64_encode(chr(0x04) . $sig);
    }

    /** Apply panel email settings to Laravel's mailer at runtime. */
    public static function applyMailConfig(): void
    {
        $driver = self::get('email', 'driver');
        $from = self::get('email', 'from_address');
        if ($from) {
            config(['mail.from.address' => $from, 'mail.from.name' => self::get('email', 'from_name') ?: config('app.name')]);
        }
        if ($driver === 'smtp' && self::get('email', 'smtp_host')) {
            $enc = self::get('email', 'smtp_encryption');
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => self::get('email', 'smtp_host'),
                'mail.mailers.smtp.port' => (int) self::get('email', 'smtp_port', 587),
                'mail.mailers.smtp.scheme' => $enc === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.encryption' => $enc === 'none' ? null : $enc,
                'mail.mailers.smtp.username' => self::get('email', 'smtp_username'),
                'mail.mailers.smtp.password' => self::get('email', 'smtp_password'),
            ]);
        } elseif ($driver === 'ses' && self::get('email', 'ses_access_key')) {
            // The admin has no AWS SDK, so it sends through the SES SMTP endpoint.
            // SES SMTP needs an SMTP password derived from the secret key (AWS SigV4 algorithm).
            $region = self::get('email', 'ses_region') ?: 'ap-south-1';
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => "email-smtp.{$region}.amazonaws.com",
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.scheme' => 'smtp',
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => self::get('email', 'ses_access_key'),
                'mail.mailers.smtp.password' => self::sesSmtpPassword((string) self::get('email', 'ses_secret_key'), $region),
            ]);
        } elseif ($driver === 'log') {
            config(['mail.default' => 'log']);
        }
    }
}
