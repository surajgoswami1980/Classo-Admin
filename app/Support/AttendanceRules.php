<?php

namespace App\Support;

use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Attendance rules stored in schools.settings['attendance'].
 * Mirrors src/modules/attendance/attendance-config.service.ts in the API —
 * keep the defaults identical.
 */
class AttendanceRules
{
    public const DEFAULTS = [
        'timezone' => 'Asia/Kolkata',
        'weekly_off' => [0],
        'saturday_off_weeks' => [],
        'self_checkin' => [
            'enabled' => false,
            'require_location' => true,
            'require_face' => false,
            'require_checkout' => false,
            'bind_device' => true,
        ],
        'location' => ['lat' => null, 'lng' => null, 'radius_m' => 150, 'max_accuracy_m' => 100],
        'timing' => [
            'start' => '09:00',
            'end' => '14:00',
            'grace_minutes' => 10,
            'checkin_opens_minutes' => 60,
            'half_day_after_minutes' => 0,
        ],
        'face' => ['threshold' => 90],
        'biometric' => ['enabled' => false],
    ];

    public static function forSchool(?int $schoolId): array
    {
        $settings = $schoolId ? (School::find($schoolId)?->settings ?? []) : [];
        $saved = $settings['attendance'] ?? [];
        $d = self::DEFAULTS;

        $weeklyOff = $saved['weekly_off'] ?? null;
        if ($weeklyOff === null && !empty($settings['working_days']) && is_array($settings['working_days'])) {
            $names = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $weeklyOff = array_values(array_keys(array_filter($names, fn ($n) => !in_array($n, $settings['working_days'], true))));
        }

        return [
            'timezone' => $saved['timezone'] ?? $d['timezone'],
            'weekly_off' => array_map('intval', $weeklyOff ?? $d['weekly_off']),
            'saturday_off_weeks' => array_map('intval', $saved['saturday_off_weeks'] ?? $d['saturday_off_weeks']),
            'self_checkin' => array_merge($d['self_checkin'], $saved['self_checkin'] ?? []),
            'location' => array_merge($d['location'], $saved['location'] ?? []),
            'timing' => array_merge($d['timing'], $saved['timing'] ?? []),
            'face' => array_merge($d['face'], $saved['face'] ?? []),
            'biometric' => array_merge($d['biometric'], $saved['biometric'] ?? []),
        ];
    }

    public static function save(int $schoolId, array $rules): void
    {
        $school = School::findOrFail($schoolId);
        $settings = $school->settings ?? [];
        $settings['attendance'] = $rules;
        $school->settings = $settings;
        $school->save();
    }

    public static function today(?int $schoolId): string
    {
        return Carbon::now(self::forSchool($schoolId)['timezone'])->toDateString();
    }

    public static function isWeeklyOff(array $rules, string $date): bool
    {
        $c = Carbon::parse($date);
        $dow = $c->dayOfWeek; // 0 = Sunday
        if (in_array($dow, $rules['weekly_off'], true)) {
            return true;
        }
        if ($dow === 6 && !empty($rules['saturday_off_weeks'])) {
            return in_array((int) ceil($c->day / 7), $rules['saturday_off_weeks'], true);
        }
        return false;
    }

    /**
     * Why a date is not a working day for an audience ('students'|'staff'), or null if it is one.
     *
     * @return array{kind:string,title:string}|null
     */
    public static function dayOff(?int $schoolId, string $date, string $audience): ?array
    {
        if (!$schoolId) {
            return null;
        }
        $holiday = DB::table('holidays')
            ->where('school_id', $schoolId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereIn('applies_to', ['all', $audience])
            ->first();
        if ($holiday) {
            return ['kind' => 'holiday', 'title' => $holiday->title];
        }
        if (self::isWeeklyOff(self::forSchool($schoolId), $date)) {
            return ['kind' => 'weekly_off', 'title' => 'Weekly off'];
        }
        return null;
    }

    public static function offMessage(string $date, array $off): string
    {
        $d = Carbon::parse($date)->format('d M Y');
        return $off['kind'] === 'holiday'
            ? "{$d} is a holiday ({$off['title']}). Attendance can't be marked."
            : "{$d} is a weekly off day. Attendance can't be marked.";
    }
}
