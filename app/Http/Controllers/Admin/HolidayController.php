<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AttendanceRules;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Holidays & weekly offs for the current school.
 * Students / parents / staff see these on their attendance calendars, and
 * attendance cannot be marked on them.
 */
class HolidayController extends Controller
{
    public const TYPES = [
        'holiday' => 'Holiday',
        'vacation' => 'Vacation',
        'exam_break' => 'Exam break',
        'other' => 'Other',
    ];

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
        $month = Carbon::createFromFormat('Y-m', $request->query('month', Carbon::now($rules['timezone'])->format('Y-m')))->startOfMonth();

        $holidays = DB::table('holidays')
            ->where('school_id', $schoolId)
            ->orderBy('start_date')
            ->get();

        // Calendar cells for the selected month
        $monthHolidays = $holidays->filter(fn ($h) => $h->start_date <= $month->copy()->endOfMonth()->toDateString() && $h->end_date >= $month->toDateString());
        $days = [];
        foreach (CarbonPeriod::create($month, $month->copy()->endOfMonth()) as $d) {
            $ds = $d->toDateString();
            $h = $monthHolidays->first(fn ($x) => $x->start_date <= $ds && $x->end_date >= $ds);
            $days[] = [
                'date' => $ds,
                'day' => $d->day,
                'dow' => $d->dayOfWeekIso, // 1 = Mon
                'holiday' => $h,
                'weekly_off' => !$h && AttendanceRules::isWeeklyOff($rules, $ds),
                'today' => $ds === AttendanceRules::today($schoolId),
            ];
        }

        $today = AttendanceRules::today($schoolId);
        $upcoming = $holidays->filter(fn ($h) => $h->end_date >= $today)->values();
        $past = $holidays->filter(fn ($h) => $h->end_date < $today)->sortByDesc('start_date')->values();
        $yearCount = $holidays->filter(fn ($h) => substr($h->start_date, 0, 4) === substr($today, 0, 4))
            ->sum(fn ($h) => Carbon::parse($h->start_date)->diffInDays(Carbon::parse($h->end_date)) + 1);

        return view('admin.holidays.index', [
            'month' => $month,
            'days' => $days,
            'upcoming' => $upcoming,
            'past' => $past,
            'rules' => $rules,
            'types' => self::TYPES,
            'yearCount' => (int) $yearCount,
        ]);
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId();
        $v = $this->validated($request);

        DB::table('holidays')->insert($v + [
            'school_id' => $schoolId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->boolean('notify')) {
            $this->announce($schoolId, $v);
        }

        return redirect()->route(panel_prefix() . '.holidays.index', ['month' => substr($v['start_date'], 0, 7)])
            ->with('success', "Holiday \"{$v['title']}\" added.");
    }

    public function update(Request $request, int $id)
    {
        $schoolId = $this->schoolId();
        $v = $this->validated($request);
        $n = DB::table('holidays')->where('id', $id)->where('school_id', $schoolId)->update($v + ['updated_at' => now()]);
        abort_unless($n || DB::table('holidays')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        return back()->with('success', 'Holiday updated.');
    }

    public function destroy(int $id)
    {
        $schoolId = $this->schoolId();
        DB::table('holidays')->where('id', $id)->where('school_id', $schoolId)->delete();
        return back()->with('success', 'Holiday removed.');
    }

    /** Weekly offs (Sunday, 2nd/4th Saturday…) live in attendance rules. */
    public function weeklyOff(Request $request)
    {
        $schoolId = $this->schoolId();
        $v = $request->validate([
            'weekly_off' => 'nullable|array',
            'weekly_off.*' => 'integer|between:0,6',
            'saturday_off_weeks' => 'nullable|array',
            'saturday_off_weeks.*' => 'integer|between:1,5',
        ]);
        $rules = AttendanceRules::forSchool($schoolId);
        $rules['weekly_off'] = array_values(array_unique(array_map('intval', $v['weekly_off'] ?? [])));
        $rules['saturday_off_weeks'] = in_array(6, $rules['weekly_off'], true)
            ? [] // every Saturday off already
            : array_values(array_unique(array_map('intval', $v['saturday_off_weeks'] ?? [])));
        AttendanceRules::save($schoolId, $rules);

        return back()->with('success', 'Weekly off days saved.');
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
            'applies_to' => 'required|in:all,students,staff',
        ], ['end_date.after_or_equal' => 'End date must be on or after the start date.']);

        $v['end_date'] = $v['end_date'] ?? $v['start_date'];
        if (Carbon::parse($v['start_date'])->diffInDays(Carbon::parse($v['end_date'])) > 120) {
            abort(back()->withInput()->withErrors(['end_date' => 'A holiday can be at most 120 days long.']));
        }
        return [
            'title' => trim($v['title']),
            'description' => $v['description'] ?? null,
            'start_date' => Carbon::parse($v['start_date'])->toDateString(),
            'end_date' => Carbon::parse($v['end_date'])->toDateString(),
            'type' => $v['type'],
            'applies_to' => $v['applies_to'],
        ];
    }

    /** In-app + push notification to the audience, via the existing push worker. */
    private function announce(int $schoolId, array $h): void
    {
        $range = $h['start_date'] === $h['end_date']
            ? Carbon::parse($h['start_date'])->format('D, d M Y')
            : Carbon::parse($h['start_date'])->format('d M') . ' – ' . Carbon::parse($h['end_date'])->format('d M Y');

        $targets = match ($h['applies_to']) {
            'students' => [['role', 'student'], ['role', 'parent']],
            'staff' => [['role', 'teacher'], ['role', 'staff']],
            default => [['all', null]],
        };
        foreach ($targets as [$type, $role]) {
            $id = DB::table('notifications')->insertGetId([
                'school_id' => $schoolId,
                'title' => "Holiday: {$h['title']}",
                'body' => "School will remain closed on {$range}." . ($h['description'] ? " {$h['description']}" : ''),
                'channel' => 'push',
                'target_type' => $type,
                'target_role' => $role,
                'sent_by' => auth()->id(),
                'status' => 'queued',
                'created_at' => now(),
            ]);
            NotificationController::queuePush($id, $schoolId);
        }
    }
}
