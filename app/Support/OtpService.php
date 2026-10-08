<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Redis/Cache-backed OTP generation + verification for the admin panel.
 *
 * Mirrors the NestJS API's OtpService: 6-digit codes, 5-min TTL, resend
 * cooldown, per-hour cap, attempt limit, and a configurable SUPER_OTP that
 * always verifies (demos/QA). Uses Laravel's cache store (set to redis via
 * CACHE_STORE=redis, or database by default) so it works even without Redis.
 */
class OtpService
{
    private const TTL_SECONDS = 300;        // 5 minutes
    private const RESEND_COOLDOWN = 30;     // seconds
    private const MAX_ATTEMPTS = 5;
    private const MAX_SENDS_PER_HOUR = 8;

    private function codeKey(string $channel, string $destination): string
    {
        return 'otp:code:' . $channel . ':' . strtolower($destination);
    }

    private function cooldownKey(string $channel, string $destination): string
    {
        return 'otp:cooldown:' . $channel . ':' . strtolower($destination);
    }

    private function rateKey(string $channel, string $destination): string
    {
        return 'otp:rate:' . $channel . ':' . strtolower($destination);
    }

    private function superOtp(): ?string
    {
        $v = trim((string) env('SUPER_OTP', ''));
        return $v !== '' ? $v : null;
    }

    private function devEcho(): bool
    {
        $explicit = env('OTP_DEV_ECHO');
        if ($explicit !== null) {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }
        return app()->environment() !== 'production';
    }

    /**
     * Generate + deliver an OTP.
     *
     * @return array{sent:bool, cooldown:int, dev_otp?:string}
     * @throws \RuntimeException on cooldown / rate-limit (message is user-safe)
     */
    public function request(string $channel, string $destination, ?string $recipientName = null): array
    {
        if (Cache::has($this->cooldownKey($channel, $destination))) {
            throw new \RuntimeException('Please wait a few seconds before requesting another OTP.');
        }

        $rateKey = $this->rateKey($channel, $destination);
        $sends = (int) Cache::increment($rateKey);
        if ($sends === 1) {
            Cache::put($rateKey, 1, now()->addHour());
        }
        if ($sends > self::MAX_SENDS_PER_HOUR) {
            throw new \RuntimeException('Too many OTP requests. Please try again later.');
        }

        $code = (string) random_int(100000, 999999);
        Cache::put(
            $this->codeKey($channel, $destination),
            ['hash' => hash('sha256', $code), 'attempts' => 0],
            now()->addSeconds(self::TTL_SECONDS),
        );
        Cache::put($this->cooldownKey($channel, $destination), 1, now()->addSeconds(self::RESEND_COOLDOWN));

        $message = "Your School ERP login OTP is {$code}. Valid for 5 minutes. Do not share it with anyone.";

        if ($channel === 'email') {
            $this->sendEmail($destination, $code, $recipientName);
        } else {
            $this->sendSms($destination, $message);
        }

        if ($this->devEcho()) {
            Log::warning("[DEV] Admin OTP for {$channel} {$destination}: {$code}");
        }

        return array_filter([
            'sent' => true,
            'cooldown' => self::RESEND_COOLDOWN,
            'dev_otp' => $this->devEcho() ? $code : null,
        ], fn ($v) => $v !== null);
    }

    /**
     * Verify an OTP. Consumes it on success. Honors SUPER_OTP.
     *
     * @throws \RuntimeException with a user-safe message on failure
     */
    public function verify(string $channel, string $destination, string $code): bool
    {
        $superOtp = $this->superOtp();
        if ($superOtp !== null && hash_equals($superOtp, $code)) {
            Cache::forget($this->codeKey($channel, $destination));
            return true;
        }

        $key = $this->codeKey($channel, $destination);
        $data = Cache::get($key);
        if (!$data) {
            throw new \RuntimeException('OTP expired or not requested. Please request a new one.');
        }

        if (($data['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            Cache::forget($key);
            throw new \RuntimeException('Too many incorrect attempts. Please request a new OTP.');
        }

        if (!hash_equals($data['hash'], hash('sha256', $code))) {
            $data['attempts'] = ($data['attempts'] ?? 0) + 1;
            Cache::put($key, $data, now()->addSeconds(self::TTL_SECONDS));
            $left = self::MAX_ATTEMPTS - $data['attempts'];
            throw new \RuntimeException("Incorrect OTP. {$left} attempt(s) left.");
        }

        Cache::forget($key);
        return true;
    }

    private function sendEmail(string $to, string $code, ?string $name): void
    {
        try {
            Mail::html(
                "<p>Hi " . e($name ?: 'there') . ",</p><p>Your One-Time Password is:</p>" .
                "<h2 style=\"letter-spacing:4px\">{$code}</h2>" .
                "<p>It is valid for 5 minutes. Do not share it with anyone.</p>",
                function ($m) use ($to) {
                    $m->to($to)->subject('Your School ERP OTP');
                },
            );
        } catch (\Throwable $e) {
            Log::warning("OTP email to {$to} failed (will rely on dev echo): {$e->getMessage()}");
        }
    }

    private function sendSms(string $phone, string $message): void
    {
        $provider = strtolower((string) env('SMS_PROVIDER', 'log'));
        $urlTemplate = env('SMS_API_URL');

        if ($provider === 'log' || !$urlTemplate) {
            Log::warning("SMS gateway not configured — would have sent to {$phone}: {$message}");
            return;
        }

        try {
            $digits = preg_replace('/\D/', '', $phone);
            $url = str_replace(
                ['{phone}', '{message}', '{key}', '{sender}'],
                [rawurlencode($digits), rawurlencode($message), rawurlencode((string) env('SMS_API_KEY', '')), rawurlencode((string) env('SMS_SENDER_ID', ''))],
                $urlTemplate,
            );
            \Illuminate\Support\Facades\Http::timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::error("OTP SMS to {$phone} failed: {$e->getMessage()}");
        }
    }
}
