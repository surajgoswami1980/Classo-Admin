<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Central upload helper.
 *
 * Decides where an uploaded file goes:
 *   - If S3 is enabled + configured (Integrations → Storage) → upload to S3,
 *     and return the FULL public S3 URL (stored in the DB as-is).
 *   - Otherwise → keep the existing local "public" disk behaviour and return
 *     the relative path (unchanged from before).
 *
 * Paths are school-wise:
 *   - A school user:  {school-code}/{folder}/...  e.g. "dav/assignments/ab12.pdf"
 *   - Super admin   :  superadmin/{folder}/...     e.g. "superadmin/images/x.png"
 *
 * Reading back: callers store whatever this returns. For display, use
 * UploadService::url($stored) which returns the S3 URL untouched or prefixes
 * the local path with "storage/". Existing local records keep working.
 */
class UploadService
{
    /**
     * Store an uploaded file and return the value to persist in the DB.
     *
     * @param  string       $folder    logical folder, e.g. "assignments", "events", "banners", "store", "images"
     * @param  int|null     $schoolId  overrides the current school (optional)
     * @return string|null             full S3 URL, or local relative path, or null when no file
     */
    public static function store(UploadedFile $file, string $folder, ?int $schoolId = null): ?string
    {
        $prefix = self::prefix($schoolId);
        $dir = trim($prefix . '/' . trim($folder, '/'), '/');

        if (self::usingS3()) {
            self::bootS3Disk();
            // Unique, safe filename. keep the real extension.
            $name = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs($dir, $name, ['disk' => 's3', 'visibility' => 'public']);

            return Storage::disk('s3')->url($path);
        }

        // Local public disk — same behaviour as before, but with the
        // school-wise prefix so it's consistent with S3.
        return $file->store($dir, 'public');
    }

    /**
     * Resolve a stored value to a browser URL.
     * - Full URLs (S3/CDN) are returned unchanged.
     * - Local relative paths get the "storage/" prefix via asset().
     */
    public static function url(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        if (Str::startsWith($stored, ['http://', 'https://'])) {
            return $stored;
        }

        return asset('storage/' . ltrim($stored, '/'));
    }

    /**
     * Delete a previously stored file (best-effort). Handles both an S3 URL
     * and a local relative path. External/foreign URLs are left alone unless
     * they belong to our configured S3 bucket.
     */
    public static function delete(?string $stored): void
    {
        if ($stored === null || $stored === '') {
            return;
        }

        if (Str::startsWith($stored, ['http://', 'https://'])) {
            if (self::usingS3()) {
                self::bootS3Disk();
                $base = rtrim((string) PlatformSettings::get('storage', 'url') ?: Storage::disk('s3')->url(''), '/');
                if ($base && Str::startsWith($stored, $base)) {
                    $key = ltrim(Str::after($stored, $base), '/');
                    if ($key !== '' && Storage::disk('s3')->exists($key)) {
                        Storage::disk('s3')->delete($key);
                    }
                }
            }

            return;
        }

        if (Storage::disk('public')->exists($stored)) {
            Storage::disk('public')->delete($stored);
        }
    }

    /** Is the S3 driver turned on and fully configured? */
    public static function usingS3(): bool
    {
        return PlatformSettings::bool('storage', 'enabled')
            && PlatformSettings::get('storage', 'key')
            && PlatformSettings::get('storage', 'secret')
            && PlatformSettings::get('storage', 'bucket')
            && PlatformSettings::get('storage', 'region');
    }

    /**
     * The school-wise (or super-admin) path prefix.
     * Uses the school's unique `code` (lowercased, slugged); falls back to the
     * numeric id, and to "superadmin" when there is no school in context.
     */
    public static function prefix(?int $schoolId = null): string
    {
        $schoolId = $schoolId ?? current_school_id();

        if (!$schoolId) {
            return 'superadmin';
        }

        $code = \App\Models\School::whereKey($schoolId)->value('code');
        $slug = $code ? Str::slug($code) : null;

        return $slug ?: ('school-' . $schoolId);
    }

    /**
     * Register the "s3" disk at runtime from the credentials saved in the
     * Integrations → Storage panel (there is no static filesystems.php config
     * with live keys). Safe to call repeatedly.
     */
    public static function bootS3Disk(): void
    {
        $config = [
            'driver' => 's3',
            'key' => PlatformSettings::get('storage', 'key'),
            'secret' => PlatformSettings::get('storage', 'secret'),
            'region' => PlatformSettings::get('storage', 'region'),
            'bucket' => PlatformSettings::get('storage', 'bucket'),
            'endpoint' => PlatformSettings::get('storage', 'endpoint') ?: null,
            'use_path_style_endpoint' => PlatformSettings::bool('storage', 'path_style'),
            'url' => PlatformSettings::get('storage', 'url') ?: null,
            'throw' => true,
            'visibility' => 'public',
        ];

        config(['filesystems.disks.s3' => $config]);
    }
}
