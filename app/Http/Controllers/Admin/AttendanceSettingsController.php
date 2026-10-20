<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AttendanceRules;
use App\Support\PlatformSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Attendance → Settings: timings, late rule, self check-in (location / face),
 * face approvals, phone resets and fingerprint machines.
 */
class AttendanceSettingsController extends Controller
{
    private function schoolId(): int
    {
        $id = current_school_id();
        abort_unless($id, 422, 'Select a school (use "Switch School") first.');
        return $id;
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();
        $rules = AttendanceRules::forSchool($schoolId);
        $tab = in_array($request->query('tab'), ['rules', 'faces', 'devices', 'biometric'], true) ? $request->query('tab') : 'rules';

        $staff = DB::table('users')
            ->where('school_id', $schoolId)
            ->whereIn('id', fn ($q) => $q->select('model_id')->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('roles.name', ['teacher', 'incharge', 'staff', 'sub-admin', 'school-admin']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'phone']);

        $faces = DB::table('staff_face_profiles as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->where('f.school_id', $schoolId)
            ->orderByRaw("FIELD(f.status, 'pending', 'rejected', 'approved')")
            ->orderByDesc('f.updated_at')
            ->get(['f.id', 'f.user_id', 'f.status', 'f.updated_at', 'f.reject_reason', 'u.name', 'u.employee_id']);

        $devices = DB::table('staff_devices as d')
            ->join('users as u', 'u.id', '=', 'd.user_id')
            ->where('d.school_id', $schoolId)
            ->orderBy('u.name')
            ->get(['d.id', 'd.user_id', 'd.device_name', 'd.platform', 'd.last_used_at', 'd.created_at', 'u.name', 'u.employee_id']);

        $machines = DB::table('biometric_devices')->where('school_id', $schoolId)->orderBy('name')->get();
        $maps = DB::table('biometric_user_maps')->where('school_id', $schoolId)->pluck('device_pin', 'user_id');
        $unmapped = DB::table('biometric_punches')
            ->where('school_id', $schoolId)->where('result', 'unmapped')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('device_pin, COUNT(*) as punches, MAX(punched_at) as last_punch')
            ->groupBy('device_pin')->orderByDesc('last_punch')->limit(20)->get();

        $today = AttendanceRules::today($schoolId);
        $todayStats = DB::table('staff_attendance')
            ->where('school_id', $schoolId)->whereDate('date', $today)
            ->selectRaw('method, COUNT(*) as n')->groupBy('method')->pluck('n', 'method');
        $rejectedToday = DB::table('attendance_attempts')
            ->where('school_id', $schoolId)->where('result', 'rejected')
            ->where('created_at', '>=', now()->startOfDay())->count();

        $apiBase = rtrim(preg_replace('#/api$#', '', (string) config('services.nestjs.base_url')), '/');

        return view('admin.attendance.settings', [
            'tab' => $tab,
            'rules' => $rules,
            'staff' => $staff,
            'faces' => $faces,
            'devices' => $devices,
            'machines' => $machines,
            'maps' => $maps,
            'unmapped' => $unmapped,
            'todayStats' => $todayStats,
            'rejectedToday' => $rejectedToday,
            'faceReady' => (bool) (PlatformSettings::get('email', 'ses_access_key') && PlatformSettings::get('email', 'ses_secret_key')),
            'apiHost' => parse_url($apiBase, PHP_URL_HOST) ?: $apiBase,
            'apiPort' => parse_url($apiBase, PHP_URL_PORT) ?: (str_starts_with($apiBase, 'https') ? 443 : 80),
        ]);
    }

    public function update(Request $request)
    {
        $schoolId = $this->schoolId();
        $v = $request->validate([
            'timing.start' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'timing.end' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'timing.grace_minutes' => 'required|integer|min:0|max:180',
            'timing.checkin_opens_minutes' => 'required|integer|min:0|max:240',
            'timing.half_day_after_minutes' => 'nullable|integer|min:0|max:600',
            'self_checkin.enabled' => 'nullable|boolean',
            'self_checkin.require_location' => 'nullable|boolean',
            'self_checkin.require_face' => 'nullable|boolean',
            'self_checkin.require_checkout' => 'nullable|boolean',
            'self_checkin.bind_device' => 'nullable|boolean',
            'location.lat' => 'nullable|numeric|between:-90,90',
            'location.lng' => 'nullable|numeric|between:-180,180',
            'location.radius_m' => 'required|integer|min:30|max:5000',
            'location.max_accuracy_m' => 'required|integer|min:10|max:500',
            'face.threshold' => 'required|integer|min:80|max:99',
            'biometric.enabled' => 'nullable|boolean',
        ], [
            'timing.start.regex' => 'Start time must be HH:MM.',
            'timing.end.regex' => 'End time must be HH:MM.',
        ]);

        if ($v['timing']['end'] <= $v['timing']['start']) {
            return back()->withInput()->withErrors(['timing.end' => 'School end time must be after the start time.']);
        }

        $rules = AttendanceRules::forSchool($schoolId);
        $selfOn = $request->boolean('self_checkin.enabled');
        $needLoc = $request->boolean('self_checkin.require_location');
        $lat = $v['location']['lat'] ?? null;
        $lng = $v['location']['lng'] ?? null;
        if ($selfOn && $needLoc && ($lat === null || $lng === null)) {
            return back()->withInput()->withErrors(['location.lat' => 'Set the school location on the map before requiring location.']);
        }
        if ($request->boolean('self_checkin.require_face') && !(PlatformSettings::get('email', 'ses_access_key'))) {
            return back()->withInput()->withErrors(['self_checkin.require_face' => 'Face check needs AWS keys. Ask the platform admin to add them in Integrations → Email (Amazon SES).']);
        }

        $rules['timing'] = [
            'start' => $v['timing']['start'],
            'end' => $v['timing']['end'],
            'grace_minutes' => (int) $v['timing']['grace_minutes'],
            'checkin_opens_minutes' => (int) $v['timing']['checkin_opens_minutes'],
            'half_day_after_minutes' => (int) ($v['timing']['half_day_after_minutes'] ?? 0),
        ];
        $rules['self_checkin'] = [
            'enabled' => $selfOn,
            'require_location' => $needLoc,
            'require_face' => $request->boolean('self_checkin.require_face'),
            'require_checkout' => $request->boolean('self_checkin.require_checkout'),
            'bind_device' => $request->boolean('self_checkin.bind_device'),
        ];
        $rules['location'] = [
            'lat' => $lat !== null ? round((float) $lat, 7) : null,
            'lng' => $lng !== null ? round((float) $lng, 7) : null,
            'radius_m' => (int) $v['location']['radius_m'],
            'max_accuracy_m' => (int) $v['location']['max_accuracy_m'],
        ];
        $rules['face'] = ['threshold' => (int) $v['face']['threshold']];
        $rules['biometric'] = ['enabled' => $request->boolean('biometric.enabled')];

        AttendanceRules::save($schoolId, $rules);

        return back()->with('success', 'Attendance settings saved. Apps pick them up within a minute.');
    }

    // ─── Face approvals ─────────────────────────────────────────

    public function facePhoto(int $id)
    {
        $schoolId = $this->schoolId();
        $img = DB::table('staff_face_profiles')->where('id', $id)->where('school_id', $schoolId)->value('image');
        abort_unless($img, 404);
        return response(base64_decode($img), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function faceReview(Request $request, int $id)
    {
        $schoolId = $this->schoolId();
        $v = $request->validate([
            'decision' => 'required|in:approve,reject,reset',
            'reason' => 'nullable|string|max:255',
        ]);
        $q = DB::table('staff_face_profiles')->where('id', $id)->where('school_id', $schoolId);
        abort_unless($q->exists(), 404);

        if ($v['decision'] === 'reset') {
            $q->delete();
            return back()->with('success', 'Face removed. The staff member can register again.');
        }
        $q->update([
            'status' => $v['decision'] === 'approve' ? 'approved' : 'rejected',
            'reject_reason' => $v['decision'] === 'reject' ? ($v['reason'] ?: 'Photo not clear') : null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', $v['decision'] === 'approve' ? 'Face approved.' : 'Face rejected.');
    }

    // ─── Phone binding ──────────────────────────────────────────

    public function resetDevice(int $id)
    {
        $schoolId = $this->schoolId();
        DB::table('staff_devices')->where('id', $id)->where('school_id', $schoolId)->delete();
        return back()->with('success', 'Phone unlinked. The next check-in will link the new phone.');
    }

    // ─── Fingerprint machines ───────────────────────────────────

    public function storeMachine(Request $request)
    {
        $schoolId = $this->schoolId();
        $v = $request->validate([
            'serial_number' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]{4,60}$/', 'unique:biometric_devices,serial_number'],
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:150',
        ], [
            'serial_number.unique' => 'This machine is already registered (maybe for another school).',
            'serial_number.regex' => 'Serial number: letters, numbers, - and _ only.',
        ]);
        DB::table('biometric_devices')->insert([
            'school_id' => $schoolId,
            'serial_number' => strtoupper(trim($v['serial_number'])),
            'name' => $v['name'],
            'location' => $v['location'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route(panel_prefix() . '.attendance.settings', ['tab' => 'biometric'])->with('success', 'Fingerprint machine added.');
    }

    public function destroyMachine(int $id)
    {
        $schoolId = $this->schoolId();
        DB::table('biometric_devices')->where('id', $id)->where('school_id', $schoolId)->delete();
        return back()->with('success', 'Machine removed.');
    }

    /** Map machine user IDs (PINs) to staff. */
    public function saveMaps(Request $request)
    {
        $schoolId = $this->schoolId();
        $v = $request->validate(['pin' => 'array', 'pin.*' => 'nullable|string|max:30|regex:/^[0-9A-Za-z]+$/']);
        $pins = collect($v['pin'] ?? [])->map(fn ($p) => trim((string) $p))->filter();

        $dupes = $pins->duplicates();
        if ($dupes->isNotEmpty()) {
            return back()->withErrors(['pin' => 'Each machine ID can belong to only one person. Duplicate: ' . $dupes->first()]);
        }
        $validUsers = DB::table('users')->where('school_id', $schoolId)->whereIn('id', $pins->keys())->pluck('id')->all();

        DB::transaction(function () use ($schoolId, $pins, $validUsers, $v) {
            DB::table('biometric_user_maps')->where('school_id', $schoolId)->whereIn('user_id', array_keys($v['pin'] ?? []))->delete();
            foreach ($pins as $userId => $pin) {
                if (!in_array((int) $userId, $validUsers, true)) {
                    continue;
                }
                DB::table('biometric_user_maps')->insert([
                    'school_id' => $schoolId, 'device_pin' => $pin, 'user_id' => (int) $userId,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                // Re-link past punches that arrived before the mapping existed.
                DB::table('biometric_punches')->where('school_id', $schoolId)->where('device_pin', $pin)
                    ->where('result', 'unmapped')->update(['user_id' => (int) $userId, 'result' => 'ignored']);
            }
        });

        return back()->with('success', 'Machine IDs saved. New punches will be applied automatically.');
    }

    /** Attempts audit — accepted + rejected self check-ins. */
    public function attempts(Request $request)
    {
        $schoolId = $this->schoolId();
        $rows = DB::table('attendance_attempts as a')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->where('a.school_id', $schoolId)
            ->when($request->query('result'), fn ($q, $r) => $q->where('a.result', $r))
            ->orderByDesc('a.created_at')
            ->limit(200)
            ->get(['a.*', 'u.name']);
        return response()->json(['data' => $rows]);
    }
}
