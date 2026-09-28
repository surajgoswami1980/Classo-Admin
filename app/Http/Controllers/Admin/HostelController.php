<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hostel management: blocks, rooms and student allocations, scoped to the
 * current school via current_school_id().
 */
class HostelController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing the hostel.');

        return $schoolId;
    }

    public function index()
    {
        $schoolId = $this->schoolId();

        $blocks = DB::table('hostel_blocks as b')
            ->when($schoolId, fn ($q) => $q->where('b.school_id', $schoolId))
            ->select([
                'b.*',
                DB::raw('(SELECT COUNT(*) FROM hostel_rooms r WHERE r.block_id = b.id) as room_count'),
                DB::raw("(SELECT COUNT(*) FROM hostel_allocations a JOIN hostel_rooms r2 ON r2.id = a.room_id WHERE r2.block_id = b.id AND a.status = 'active') as occupied"),
            ])
            ->orderBy('b.name')
            ->get();

        $rooms = DB::table('hostel_rooms as r')
            ->join('hostel_blocks as b', 'b.id', '=', 'r.block_id')
            ->when($schoolId, fn ($q) => $q->where('r.school_id', $schoolId))
            ->select([
                'r.*', 'b.name as block_name',
                DB::raw("(SELECT COUNT(*) FROM hostel_allocations a WHERE a.room_id = r.id AND a.status = 'active') as occupied"),
            ])
            ->orderBy('b.name')->orderBy('r.room_number')
            ->get();

        $allocations = DB::table('hostel_allocations as a')
            ->join('students as s', 's.id', '=', 'a.student_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->join('hostel_rooms as r', 'r.id', '=', 'a.room_id')
            ->join('hostel_blocks as b', 'b.id', '=', 'r.block_id')
            ->when($schoolId, fn ($q) => $q->where('a.school_id', $schoolId))
            ->where('a.status', 'active')
            ->select(['a.*', 'u.name as student_name', 's.roll_number', 'r.room_number', 'b.name as block_name'])
            ->orderBy('b.name')->orderBy('r.room_number')
            ->get();

        $students = DB::table('students as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('classes as c', 'c.id', '=', 's.class_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 's.section_id')
            ->when($schoolId, fn ($q) => $q->where('s.school_id', $schoolId))
            ->where('s.status', 'active')
            ->select(['s.id', 'u.name', 'c.name as class_name', 'sec.name as section_name'])
            ->orderBy('u.name')
            ->get();

        $stats = [
            'blocks' => $blocks->count(),
            'rooms'  => $rooms->count(),
            'capacity' => $rooms->sum('capacity'),
            'occupied' => $allocations->count(),
        ];

        return view('admin.hostel.index', compact('blocks', 'rooms', 'allocations', 'students', 'stats'));
    }

    public function storeBlock(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:boys,girls,mixed',
            'warden_name' => 'nullable|string|max:100',
            'warden_phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
        ]);

        DB::table('hostel_blocks')->insert($validated + [
            'school_id' => $schoolId, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Block created.');
    }

    public function storeRoom(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'block_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('hostel_blocks', 'id')->where('school_id', $schoolId)],
            'room_number' => 'required|string|max:30',
            'room_type' => 'required|in:single,double,triple,dormitory',
            'capacity' => 'required|integer|min:1',
            'fee_per_month' => 'nullable|numeric|min:0',
        ]);

        DB::table('hostel_rooms')->insert($validated + [
            'school_id' => $schoolId, 'is_active' => 1,
            'fee_per_month' => $validated['fee_per_month'] ?? 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Room added.');
    }

    public function allocate(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'room_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('hostel_rooms', 'id')->where('school_id', $schoolId)],
            'student_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'allocated_from' => 'nullable|date',
        ]);

        $room = DB::table('hostel_rooms')->where('id', $validated['room_id'])->first();
        $occupied = DB::table('hostel_allocations')->where('room_id', $validated['room_id'])->where('status', 'active')->count();
        if ($occupied >= $room->capacity) {
            return back()->withErrors(['error' => 'Room is at full capacity.']);
        }

        $alreadyAllocated = DB::table('hostel_allocations')->where('student_id', $validated['student_id'])->where('status', 'active')->exists();
        if ($alreadyAllocated) {
            return back()->withErrors(['error' => 'Student already has an active allocation.']);
        }

        DB::table('hostel_allocations')->insert([
            'school_id' => $schoolId,
            'room_id' => $validated['room_id'],
            'student_id' => $validated['student_id'],
            'allocated_from' => $validated['allocated_from'] ?? now()->toDateString(),
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Student allocated to room.');
    }

    public function vacate($id)
    {
        $schoolId = $this->requireSchoolId();
        abort_unless(DB::table('hostel_allocations')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        DB::table('hostel_allocations')->where('id', $id)->update([
            'status' => 'vacated', 'vacated_on' => now()->toDateString(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Room vacated.');
    }
}
