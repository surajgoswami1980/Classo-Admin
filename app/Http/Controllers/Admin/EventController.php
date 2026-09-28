<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Event management for the admin/client panel. Schools create events (free or
 * paid), publish them (which notifies students), and view registrations.
 * Scoped to the current school via current_school_id().
 */
class EventController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing events.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();
        $filters = $request->only(['status', 'category']);

        $events = DB::table('events as e')
            ->when($schoolId, fn ($q) => $q->where('e.school_id', $schoolId))
            ->when(!empty($filters['status']), fn ($q) => $q->where('e.status', $filters['status']))
            ->when(!empty($filters['category']), fn ($q) => $q->where('e.category', $filters['category']))
            ->select([
                'e.*',
                DB::raw('(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = e.id AND r.status = \'registered\') as registrations'),
            ])
            ->orderByDesc('e.start_at')
            ->paginate(20)
            ->appends($filters);

        // For audience targeting dropdowns
        $classes = DB::table('classes')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');
        $sections = DB::table('sections')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get(['id', 'name', 'class_id']);

        $stats = [
            'total'     => (int) DB::table('events')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->count(),
            'published' => (int) DB::table('events')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'published')->count(),
            'upcoming'  => (int) DB::table('events')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('start_at', '>=', now())->count(),
            'registrations' => (int) DB::table('event_registrations')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'registered')->count(),
        ];

        return view('admin.events.index', compact('events', 'classes', 'sections', 'filters', 'stats'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $this->validateEvent($request);

        DB::table('events')->insert($validated + [
            'school_id' => $schoolId,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Event created as draft. Publish it to notify students.');
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->requireSchoolId();
        $event = DB::table('events')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($event, 404, 'Event not found.');

        $validated = $this->validateEvent($request);
        DB::table('events')->where('id', $id)->update($validated + ['updated_at' => now()]);

        return back()->with('success', 'Event updated.');
    }

    public function destroy($id)
    {
        $schoolId = $this->requireSchoolId();
        abort_unless(DB::table('events')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        DB::table('events')->where('id', $id)->delete();

        return back()->with('success', 'Event deleted.');
    }

    /**
     * Publish an event and fan out a notification to the target audience.
     */
    public function publish($id)
    {
        $schoolId = $this->requireSchoolId();
        $event = DB::table('events')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($event, 404, 'Event not found.');

        DB::table('events')->where('id', $id)->update(['status' => 'published', 'updated_at' => now()]);

        // Fan-out notification (matches the API notification schema).
        $target = match ($event->audience_type) {
            'section' => ['target_type' => 'section', 'target_section_id' => $event->section_id],
            'class'   => ['target_type' => 'class', 'target_class_id' => $event->class_id],
            default   => ['target_type' => 'role', 'target_role' => 'student'],
        };

        DB::table('notifications')->insert(array_merge([
            'school_id' => $schoolId,
            'title' => 'New Event: ' . $event->title,
            'body' => $event->title . ' on ' . \Carbon\Carbon::parse($event->start_at)->format('d M Y, h:i A')
                . ($event->is_paid ? ' — Fee ₹' . number_format($event->fee) : ' — Free') . '. Register now!',
            'channel' => 'all',
            'target_role' => null,
            'target_class_id' => null,
            'target_section_id' => null,
            'sent_by' => auth()->id(),
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
        ], $target));

        return back()->with('success', 'Event published and students notified.');
    }

    public function cancel($id)
    {
        $schoolId = $this->requireSchoolId();
        abort_unless(DB::table('events')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        DB::table('events')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);

        return back()->with('success', 'Event cancelled.');
    }

    /**
     * Registrations list for an event.
     */
    public function registrations($id)
    {
        $schoolId = $this->schoolId();
        $event = DB::table('events')->where('id', $id)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->first();
        abort_unless($event, 404, 'Event not found.');

        $registrations = DB::table('event_registrations as r')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->leftJoin('students as s', 's.id', '=', 'r.student_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->where('r.event_id', $id)
            ->select([
                'r.*', 'u.name as user_name', 'u.email', 'u.phone',
                's.roll_number', 's.admission_number', 'c.name as class_name', 'sec.name as section_name',
            ])
            ->orderByDesc('r.created_at')
            ->get();

        return view('admin.events.registrations', compact('event', 'registrations'));
    }

    private function validateEvent(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'venue' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'registration_deadline' => 'nullable|date',
            'is_paid' => 'nullable|boolean',
            'fee' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'audience_type' => 'required|in:all,class,section',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $isPaid = $request->boolean('is_paid');

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'general',
            'venue' => $data['venue'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'] ?? null,
            'registration_deadline' => $data['registration_deadline'] ?? null,
            'is_paid' => $isPaid,
            'fee' => $isPaid ? ($data['fee'] ?? 0) : 0,
            'capacity' => $data['capacity'] ?? null,
            'audience_type' => $data['audience_type'],
            'class_id' => $data['audience_type'] === 'all' ? null : ($data['class_id'] ?? null),
            'section_id' => $data['audience_type'] === 'section' ? ($data['section_id'] ?? null) : null,
        ];
    }
}
