<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    private function getSchoolId(): ?int
    {
        // current_school_id() is impersonation-aware: while a super-admin is
        // switched into a school, this scopes to that school; otherwise
        // (no impersonation, own school_id null) it stays null -> global view.
        return current_school_id();
    }

    public function index()
    {
        $schoolId = $this->getSchoolId();
        $stats = [
            'total_collected' => DB::table('payment_transactions')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'success')->sum('amount'),
            'total_pending' => DB::table('fee_invoices')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'pending')->sum('total_amount'),
            'total_overdue' => DB::table('fee_invoices')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'pending')->where('due_date', '<', now()->toDateString())->sum('total_amount'),
            'total_defaulters' => DB::table('fee_invoices')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'pending')->where('due_date', '<', now()->toDateString())->distinct('student_id')->count('student_id'),
        ];

        return view('admin.fees.index', compact('stats'));
    }

    public function structure(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $structures = DB::table('fee_structures')
            ->leftJoin('classes', 'fee_structures.class_id', '=', 'classes.id')
            ->leftJoin('academic_sessions', 'fee_structures.academic_session_id', '=', 'academic_sessions.id')
            ->select(['fee_structures.*', 'classes.name as class_name', 'academic_sessions.name as session_name'])
            ->when($schoolId, fn($q) => $q->where('fee_structures.school_id', $schoolId))
            ->orderByDesc('fee_structures.created_at')
            ->paginate(20);

        $classes = DB::table('classes')->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->pluck('name', 'id');
        $sessions = DB::table('academic_sessions')->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->pluck('name', 'id');

        return view('admin.fees.structure', compact('structures', 'classes', 'sessions'));
    }

    public function storeStructure(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'class_id' => 'required|integer|exists:classes,id',
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
            'total_amount' => 'required|numeric|min:1',
            'installment_count' => 'nullable|integer|min:1|max:12',
            'late_fee_per_day' => 'nullable|numeric|min:0',
            'late_fee_max' => 'nullable|numeric|min:0',
        ]);

        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            $schoolId = DB::table('classes')->where('id', $validated['class_id'])->value('school_id');
        }

        DB::table('fee_structures')->insert([
            'school_id' => $schoolId,
            'academic_session_id' => $validated['academic_session_id'],
            'name' => $validated['name'],
            'class_id' => $validated['class_id'],
            'total_amount' => $validated['total_amount'],
            'installment_count' => $validated['installment_count'] ?? 1,
            'late_fee_per_day' => $validated['late_fee_per_day'] ?? 0,
            'late_fee_max' => $validated['late_fee_max'] ?? 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.fees.structure')->with('success', 'Fee structure created');
    }

    public function invoices(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $filters = $request->only(['status', 'class_id', 'search']);

        $invoices = DB::table('fee_invoices')
            ->join('students', 'fee_invoices.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->select(['fee_invoices.*', 'users.name as student_name', 'classes.name as class_name', 'students.roll_number'])
            ->when($schoolId, fn($q) => $q->where('fee_invoices.school_id', $schoolId))
            ->when(!empty($filters['status']), fn($q) => $q->where('fee_invoices.status', $filters['status']))
            ->when(!empty($filters['search']), fn($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('users.name', 'like', "%{$filters['search']}%")
                   ->orWhere('fee_invoices.invoice_number', 'like', "%{$filters['search']}%");
            }))
            ->orderByDesc('fee_invoices.due_date')
            ->paginate(20)
            ->appends($filters);

        return view('admin.fees.invoices', compact('invoices', 'filters'));
    }

    public function generateInvoices(Request $request)
    {
        $validated = $request->validate([
            'fee_structure_id' => 'required|integer|exists:fee_structures,id',
            'installment_number' => 'required|integer|min:1',
            'due_date' => 'required|date|after:today',
        ]);

        $structure = DB::table('fee_structures')->where('id', $validated['fee_structure_id'])->first();
        if (!$structure) return back()->withErrors(['error' => 'Fee structure not found']);

        $students = DB::table('students')
            ->where('school_id', $structure->school_id)
            ->where('class_id', $structure->class_id)
            ->where('status', 'active')
            ->pluck('id');

        if ($students->isEmpty()) {
            return back()->withErrors(['error' => 'No active students in this class']);
        }

        $amount = round($structure->total_amount / $structure->installment_count, 2);
        $generated = 0;

        foreach ($students as $studentId) {
            $exists = DB::table('fee_invoices')
                ->where('student_id', $studentId)
                ->where('fee_structure_id', $structure->id)
                ->where('fee_installment_id', $validated['installment_number'])
                ->exists();

            if ($exists) continue;

            DB::table('fee_invoices')->insert([
                'school_id' => $structure->school_id,
                'student_id' => $studentId,
                'fee_structure_id' => $structure->id,
                'fee_installment_id' => $validated['installment_number'],
                'invoice_number' => 'INV-' . $structure->school_id . '-' . $studentId . '-' . $validated['installment_number'] . '-' . strtoupper(substr(uniqid(), -5)),
                'amount' => $amount,
                'late_fee' => 0,
                'total_amount' => $amount,
                'status' => 'pending',
                'due_date' => $validated['due_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $generated++;
        }

        return back()->with('success', "Generated {$generated} invoices for " . $students->count() . " students");
    }

    public function defaulters(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $defaulters = DB::table('fee_invoices')
            ->join('students', 'fee_invoices.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->select([
                'fee_invoices.*', 'users.name as student_name', 'users.phone',
                'classes.name as class_name', 'students.roll_number',
                DB::raw("DATEDIFF(CURDATE(), fee_invoices.due_date) as days_overdue"),
            ])
            ->when($schoolId, fn($q) => $q->where('fee_invoices.school_id', $schoolId))
            ->where('fee_invoices.status', 'pending')
            ->where('fee_invoices.due_date', '<', now()->toDateString())
            ->orderByDesc('days_overdue')
            ->paginate(20);

        return view('admin.fees.defaulters', compact('defaulters'));
    }

    /**
     * Printable invoice (unpaid) OR receipt (paid) for a single invoice.
     * Renders a self-contained, print-friendly HTML page — the browser's
     * "Save as PDF" produces a clean document with no extra dependencies.
     */
    public function invoiceDocument($id)
    {
        $schoolId = $this->getSchoolId();

        $invoice = DB::table('fee_invoices')
            ->join('students', 'fee_invoices.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->leftJoin('fee_structures', 'fee_invoices.fee_structure_id', '=', 'fee_structures.id')
            ->select([
                'fee_invoices.*',
                'users.name as student_name', 'users.email as student_email', 'users.phone as student_phone',
                'students.roll_number', 'students.admission_number',
                'students.father_name', 'students.father_phone',
                'classes.name as class_name', 'sections.name as section_name',
                'fee_structures.name as fee_name',
            ])
            ->where('fee_invoices.id', $id)
            ->when($schoolId, fn($q) => $q->where('fee_invoices.school_id', $schoolId))
            ->first();

        if (!$invoice) abort(404, 'Invoice not found');

        $school = \App\Models\School::find($invoice->school_id);

        // Payment (for receipt view), if any successful txn exists
        $payment = DB::table('payment_transactions')
            ->where('fee_invoice_id', $invoice->id)
            ->where('status', 'success')
            ->orderByDesc('created_at')
            ->first();

        $isReceipt = $invoice->status === 'paid';

        return view('admin.fees.document', compact('invoice', 'school', 'payment', 'isReceipt'));
    }

    public function collectionReport(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $filters = $request->only(['from_date', 'to_date']);
        $report = collect();

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $report = DB::table('payment_transactions')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'success')
                ->whereBetween('created_at', [$filters['from_date'], $filters['to_date'] . ' 23:59:59'])
                ->select([
                    DB::raw("DATE(created_at) as date"),
                    DB::raw("COUNT(*) as transactions"),
                    DB::raw("SUM(amount) as collected"),
                    DB::raw("SUM(CASE WHEN gateway = 'razorpay' THEN amount ELSE 0 END) as online"),
                    DB::raw("SUM(CASE WHEN gateway = 'offline' THEN amount ELSE 0 END) as offline"),
                ])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderByDesc('date')
                ->get();
        }

        return view('admin.fees.collection-report', compact('report', 'filters'));
    }
}
