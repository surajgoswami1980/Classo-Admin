<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Payroll: per-staff salary structures and monthly payslips, scoped to the
 * current school via current_school_id().
 */
class PayrollController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing payroll.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        // Staff with their salary structure (if any)
        $staff = DB::table('users as u')
            ->leftJoin('salary_structures as ss', function ($join) {
                $join->on('ss.user_id', '=', 'u.id')->on('ss.school_id', '=', 'u.school_id');
            })
            ->when($schoolId, fn ($q) => $q->where('u.school_id', $schoolId))
            ->where('u.is_active', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('model_has_roles as mhr')
                    ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                    ->whereColumn('mhr.model_id', 'u.id')
                    ->whereIn('r.name', ['teacher', 'sub-admin', 'incharge', 'school-admin']);
            })
            ->select(['u.id as user_id', 'u.name', 'u.email', 'u.designation', 'ss.id as structure_id', 'ss.basic', 'ss.hra', 'ss.allowances', 'ss.deductions', 'ss.net'])
            ->orderBy('u.name')
            ->get();

        $payslips = DB::table('payslips as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->when($schoolId, fn ($q) => $q->where('p.school_id', $schoolId))
            ->where('p.month', $month)->where('p.year', $year)
            ->select(['p.*', 'u.name as staff_name', 'u.designation'])
            ->orderBy('u.name')
            ->get();

        $summary = DB::table('payslips')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('month', $month)->where('year', $year)
            ->selectRaw("COUNT(*) as payslips, COALESCE(SUM(net),0) as total_net, COALESCE(SUM(CASE WHEN status='paid' THEN net ELSE 0 END),0) as paid_net, COALESCE(SUM(CASE WHEN status='generated' THEN net ELSE 0 END),0) as pending_net")
            ->first();

        return view('admin.payroll.index', compact('staff', 'payslips', 'summary', 'month', 'year'));
    }

    public function storeStructure(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'user_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('users', 'id')->where('school_id', $schoolId)],
            'basic' => 'required|numeric|min:0',
            'hra' => 'nullable|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);

        $basic = (float) $validated['basic'];
        $hra = (float) ($validated['hra'] ?? 0);
        $allowances = (float) ($validated['allowances'] ?? 0);
        $deductions = (float) ($validated['deductions'] ?? 0);
        $gross = $basic + $hra + $allowances;
        $net = $gross - $deductions;

        DB::table('salary_structures')->updateOrInsert(
            ['school_id' => $schoolId, 'user_id' => $validated['user_id']],
            [
                'basic' => $basic, 'hra' => $hra, 'allowances' => $allowances, 'deductions' => $deductions,
                'gross' => $gross, 'net' => $net, 'is_active' => 1, 'updated_at' => now(), 'created_at' => now(),
            ],
        );

        return back()->with('success', 'Salary structure saved.');
    }

    public function generatePayslip(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $validated = $request->validate([
            'user_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('salary_structures', 'user_id')->where('school_id', $schoolId)],
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'lop_days' => 'nullable|integer|min:0|max:31',
            'extra_deductions' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $structure = DB::table('salary_structures')->where('school_id', $schoolId)->where('user_id', $validated['user_id'])->first();
        if (!$structure) {
            return back()->withErrors(['error' => 'No salary structure for this staff member.']);
        }

        $exists = DB::table('payslips')->where('school_id', $schoolId)->where('user_id', $validated['user_id'])
            ->where('month', $validated['month'])->where('year', $validated['year'])->exists();
        if ($exists) {
            return back()->withErrors(['error' => 'Payslip already generated for this period.']);
        }

        $lopDays = (int) ($validated['lop_days'] ?? 0);
        $lopDeduction = round(((float) $structure->basic / 30) * $lopDays, 2);
        $extra = (float) ($validated['extra_deductions'] ?? 0);
        $deductions = (float) $structure->deductions + $lopDeduction + $extra;
        $net = max(0, (float) $structure->gross - $deductions);

        DB::table('payslips')->insert([
            'school_id' => $schoolId,
            'user_id' => $validated['user_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'basic' => $structure->basic,
            'hra' => $structure->hra,
            'allowances' => $structure->allowances,
            'deductions' => $deductions,
            'lop_days' => $lopDays,
            'gross' => $structure->gross,
            'net' => $net,
            'status' => 'generated',
            'remarks' => $validated['remarks'] ?? null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Payslip generated.');
    }

    public function markPaid($id)
    {
        $schoolId = $this->requireSchoolId();
        abort_unless(DB::table('payslips')->where('id', $id)->where('school_id', $schoolId)->exists(), 404);

        DB::table('payslips')->where('id', $id)->update([
            'status' => 'paid', 'paid_on' => now()->toDateString(), 'updated_at' => now(),
        ]);

        return back()->with('success', 'Payslip marked as paid.');
    }

    /**
     * Printable payslip for a single staff member/month. Self-contained
     * HTML → browser "Save as PDF". Scoped to the current school.
     */
    public function payslipDocument($id)
    {
        $schoolId = $this->schoolId();

        $payslip = DB::table('payslips as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->when($schoolId, fn ($q) => $q->where('p.school_id', $schoolId))
            ->where('p.id', $id)
            ->select(['p.*', 'u.name as staff_name', 'u.email as staff_email', 'u.phone as staff_phone', 'u.designation', 'u.department', 'u.employee_id'])
            ->first();

        if (!$payslip) abort(404, 'Payslip not found');

        $school = \App\Models\School::find($payslip->school_id);

        $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
        $monthName = $months[$payslip->month] ?? $payslip->month;

        return view('admin.payroll.document', compact('payslip', 'school', 'monthName'));
    }
}
