<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    /**
     * Mutations need a concrete school to attribute the record to — unlike
     * index() (which super-admin can view unscoped, across all schools),
     * a route/vehicle/assignment can't exist without one.
     */
    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing transport.');

        return $schoolId;
    }

    public function index()
    {
        $schoolId = $this->schoolId();

        $routes = DB::table('transport_routes as r')
            ->leftJoin('vehicles as v', 'v.id', '=', 'r.vehicle_id')
            ->when($schoolId, fn($q) => $q->where('r.school_id', $schoolId))
            ->select([
                'r.*', 'v.vehicle_number', 'v.capacity',
                DB::raw('(SELECT COUNT(*) FROM student_transport st WHERE st.route_id = r.id) as student_count'),
                DB::raw('(SELECT COUNT(*) FROM transport_stops ts WHERE ts.route_id = r.id) as stop_count'),
            ])
            ->orderBy('r.name')
            ->get();

        $vehicles = DB::table('vehicles')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('vehicle_number')
            ->get();

        $students = DB::table('students as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->when($schoolId, fn($q) => $q->where('s.school_id', $schoolId))
            ->where('s.status', 'active')
            ->select(['s.id', 'u.name', 'c.name as class_name', 'sec.name as section_name'])
            ->orderBy('u.name')
            ->get();

        $studentAssignments = DB::table('student_transport as st')
            ->join('students as s', 's.id', '=', 'st.student_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->when($schoolId, fn($q) => $q->where('st.school_id', $schoolId))
            ->select(['st.*', 'u.name as student_name'])
            ->get()
            ->groupBy('route_id');

        $sessions = DB::table('academic_sessions')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('is_current', true)
            ->pluck('id')
            ->first();

        return view('admin.transport.index', compact('routes', 'vehicles', 'students', 'studentAssignments', 'sessions'));
    }

    public function storeRoute(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'vehicle_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('vehicles', 'id')->where('school_id', $schoolId)],
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:15',
        ]);

        DB::table('transport_routes')->insert($validated + [
            'school_id' => $schoolId,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Route created successfully.');
    }

    public function storeStop(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'route_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)],
            'name' => 'required|string|max:100',
            'sequence_order' => 'required|integer|min:1',
            'pickup_time' => 'nullable|date_format:H:i',
            'drop_time' => 'nullable|date_format:H:i',
        ]);

        DB::table('transport_stops')->insert($validated + [
            'school_id' => $schoolId,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Stop added.');
    }

    public function destroyStop($id)
    {
        DB::table('transport_stops')->where('id', $id)->delete();

        return back()->with('success', 'Stop removed.');
    }

    public function storeVehicle(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:vehicles,vehicle_number',
            'capacity' => 'required|integer|min:1',
            'vehicle_type' => 'required|in:bus,van,auto',
            'insurance_expiry' => 'nullable|date',
            'fitness_expiry' => 'nullable|date',
        ]);

        DB::table('vehicles')->insert($validated + [
            'school_id' => $schoolId,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Vehicle added.');
    }

    public function assignStudent(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            // `exists` alone only checks the row exists ANYWHERE — without
            // scoping to school_id, a submitted id belonging to a different
            // school would silently pass and create a cross-tenant record.
            'student_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'route_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('transport_routes', 'id')->where('school_id', $schoolId)],
            'stop_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('transport_stops', 'id')->where('school_id', $schoolId)],
        ]);

        $sessionId = DB::table('academic_sessions')
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->value('id');

        if (!$sessionId) {
            return back()->withErrors(['error' => 'No current academic session configured for this school.']);
        }

        DB::table('student_transport')->updateOrInsert(
            ['student_id' => $validated['student_id'], 'academic_session_id' => $sessionId],
            [
                'school_id' => $schoolId,
                'route_id' => $validated['route_id'],
                'stop_id' => $validated['stop_id'],
                'created_at' => now(),
            ],
        );

        return back()->with('success', 'Student assigned to route.');
    }

    public function unassignStudent($studentTransportId)
    {
        DB::table('student_transport')->where('id', $studentTransportId)->delete();

        return back()->with('success', 'Student removed from route.');
    }
}
