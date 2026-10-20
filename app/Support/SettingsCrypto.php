<?php

namespace App\Support;

use RuntimeException;

/**
 * Encrypts platform settings so BOTH Laravel and the NestJS API can read them.
 *
 * AES-256-GCM, key = base64 32 bytes from SETTINGS_ENCRYPTION_KEY (identical in
 * both apps' .env). Stored format: v1:<iv b64>:<tag b64>:<ciphertext b64>
 *
 * The NestJS counterpart lives in src/common/providers/platform-settings.service.ts.
 */
class SettingsCrypto
{
    private const PREFIX = 'v1';

    private static function key(): string
    {
        $raw = (string) config('services.platform_settings.key');
        if (str_starts_with($raw, 'base64:')) {
            $raw = substr($raw, 7);
        }
        $key = base64_decode($raw, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('SETTINGS_ENCRYPTION_KEY is missing or invalid (expected base64 of 32 bytes).');
        }
        return $key;
    }

    public static function configured(): bool
    {
        try {
            self::key();
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($cipher === false) {
            throw new RuntimeException('Could not encrypt setting.');
        }
        return implode(':', [self::PREFIX, base64_encode($iv), base64_encode($tag), base64_encode($cipher)]);
    }

    /** Returns null when the value can't be decrypted (e.g. the key was rotated). */
    public static function decrypt(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        $parts = explode(':', $stored);
        if (count($parts) !== 4 || $parts[0] !== self::PREFIX) {
            return null;
        }
        [, $iv, $tag, $cipher] = $parts;
        $plain = openssl_decrypt(
            base64_decode($cipher),
            'aes-256-gcm',
            self::key(),
            OPENSSL_RAW_DATA,
            base64_decode($iv),
            base64_decode($tag),
        );
        return $plain === false ? null : $plain;
    }
}
