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

    // ═══════════════════════════════════════════════════════════════════
    //  Category-based fees (categories, class plans, monthly/exam generate)
    // ═══════════════════════════════════════════════════════════════════

    /** Categories + class plans management screen. */
    public function categories(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $categories = DB::table('fee_categories')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('sort_order')->orderByDesc('id')
            ->get();

        $plans = DB::table('class_fee_plans as p')
            ->leftJoin('fee_categories as c', 'c.id', '=', 'p.fee_category_id')
            ->leftJoin('classes as cl', 'cl.id', '=', 'p.class_id')
            ->when($schoolId, fn($q) => $q->where('p.school_id', $schoolId))
            ->select(['p.*', 'c.name as category_name', 'c.type as category_type', 'cl.name as class_name'])
            ->orderBy('cl.name')
            ->get();

        $classes = DB::table('classes')->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->pluck('name', 'id');

        return view('admin.fees.categories', compact('categories', 'plans', 'classes'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:monthly,one_time,exam',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $schoolId = $this->getSchoolId() ?? DB::table('users')->where('id', auth()->id())->value('school_id');

        DB::table('fee_categories')->insert([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Fee category created.');
    }

    public function deleteCategory($id)
    {
        $schoolId = $this->getSchoolId();
        DB::table('fee_categories')->where('id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->delete();
        return back()->with('success', 'Fee category deleted.');
    }

    /** Create/update a class fee plan (category amount for a class). */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'fee_category_id' => 'required|integer|exists:fee_categories,id',
            'class_id' => 'required|integer|exists:classes,id',
            'amount' => 'required|numeric|min:0',
            'due_day' => 'nullable|integer|min:1|max:28',
        ]);
        $schoolId = $this->getSchoolId() ?? DB::table('classes')->where('id', $validated['class_id'])->value('school_id');

        DB::table('class_fee_plans')->updateOrInsert(
            [
                'school_id' => $schoolId,
                'fee_category_id' => $validated['fee_category_id'],
                'class_id' => $validated['class_id'],
            ],
            [
                'amount' => $validated['amount'],
                'due_day' => $validated['due_day'] ?? 10,
                'is_active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Class fee plan saved.');
    }

    public function deletePlan($id)
    {
        $schoolId = $this->getSchoolId();
        DB::table('class_fee_plans')->where('id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->delete();
        return back()->with('success', 'Plan removed.');
    }

    /** Generate monthly OR one-time/exam invoices for a category. */
    public function generateCategory(Request $request)
    {
        $validated = $request->validate([
            'fee_category_id' => 'required|integer|exists:fee_categories,id',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
            'due_date' => 'nullable|date',
            'exam_id' => 'nullable|integer',
            'title' => 'nullable|string|max:150',
        ]);
        $schoolId = $this->getSchoolId();

        $cat = DB::table('fee_categories')->where('id', $validated['fee_category_id'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->first();
        if (!$cat) return back()->withErrors(['error' => 'Category not found']);

        $plans = DB::table('class_fee_plans')
            ->where('fee_category_id', $cat->id)->where('is_active', 1)->get();
        if ($plans->isEmpty()) return back()->withErrors(['error' => 'No class plans configured for this category']);

        $isMonthly = $cat->type === 'monthly';
        $month = $validated['month'] ?? (int) now()->format('n');
        $year = $validated['year'] ?? (int) now()->format('Y');
        $created = 0;

        foreach ($plans as $plan) {
            $students = DB::table('students')
                ->where('school_id', $plan->school_id)
                ->where('class_id', $plan->class_id)
                ->where('status', 'active')
                ->get(['id']);

            foreach ($students as $student) {
                $dupQ = DB::table('fee_invoices')
                    ->where('student_id', $student->id)
                    ->where('fee_category_id', $cat->id);
                if ($isMonthly) {
                    $dupQ->where('period_year', $year)->where('period_month', $month);
                } elseif (!empty($validated['exam_id'])) {
                    $dupQ->where('exam_id', $validated['exam_id']);
                }
                if ($dupQ->exists()) continue;

                $override = DB::table('student_fee_overrides')
                    ->where('student_id', $student->id)
                    ->where('fee_category_id', $cat->id)
                    ->value('amount');
                $amount = $override !== null ? $override : $plan->amount;

                if ($isMonthly) {
                    $dueDay = min(max((int) $plan->due_day, 1), 28);
                    $dueDate = sprintf('%04d-%02d-%02d', $year, $month, $dueDay);
                    $title = $cat->name . ' — ' . date('M', mktime(0, 0, 0, $month, 1)) . ' ' . $year;
                    $period = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
                } else {
                    $dueDate = $validated['due_date'] ?? now()->addDays(14)->toDateString();
                    $title = $validated['title'] ?? $cat->name;
                    $period = !empty($validated['exam_id']) ? 'EXM' . $validated['exam_id'] : 'OT';
                }

                DB::table('fee_invoices')->insert([
                    'school_id' => $plan->school_id,
                    'student_id' => $student->id,
                    'fee_category_id' => $cat->id,
                    'exam_id' => $validated['exam_id'] ?? null,
                    'period_year' => $isMonthly ? $year : null,
                    'period_month' => $isMonthly ? $month : null,
                    'invoice_number' => 'FEE-' . $plan->school_id . '-' . $cat->id . '-' . $student->id . '-' . $period . '-' . strtoupper(substr(uniqid(), -4)),
                    'title' => $title,
                    'amount' => $amount,
                    'late_fee' => 0,
                    'total_amount' => $amount,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            }
        }

        return back()->with('success', "Generated {$created} invoices.");
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Class-wise pending + manual (offline) payment
    // ═══════════════════════════════════════════════════════════════════

    public function pending(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $classId = $request->integer('class_id') ?: null;

        $summary = DB::table('fee_invoices as i')
            ->join('students as s', 's.id', '=', 'i.student_id')
            ->join('classes as c', 'c.id', '=', 's.class_id')
            ->when($schoolId, fn($q) => $q->where('i.school_id', $schoolId))
            ->whereIn('i.status', ['pending', 'overdue', 'partial'])
            ->when($classId, fn($q) => $q->where('c.id', $classId))
            ->groupBy('c.id', 'c.name')
            ->select([
                'c.id as class_id', 'c.name as class_name',
                DB::raw('COUNT(DISTINCT i.student_id) as students_with_dues'),
                DB::raw('COUNT(*) as pending_invoices'),
                DB::raw('SUM(i.total_amount) as pending_amount'),
            ])
            ->orderBy('c.name')
            ->get();

        $rows = collect();
        if ($classId) {
            $rows = DB::table('fee_invoices as i')
                ->join('students as s', 's.id', '=', 'i.student_id')
                ->join('users as u', 'u.id', '=', 's.user_id')
                ->leftJoin('fee_categories as fc', 'fc.id', '=', 'i.fee_category_id')
                ->when($schoolId, fn($q) => $q->where('i.school_id', $schoolId))
                ->where('s.class_id', $classId)
                ->whereIn('i.status', ['pending', 'overdue', 'partial'])
                ->select([
                    'i.id', 'i.invoice_number', 'i.title', 'i.total_amount', 'i.status', 'i.due_date',
                    'u.name as student_name', 'u.phone', 's.roll_number', 'fc.name as category_name',
                ])
                ->orderBy('i.due_date')
                ->get();
        }

        $classes = DB::table('classes')->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->pluck('name', 'id');

        return view('admin.fees.pending', compact('summary', 'rows', 'classes', 'classId'));
    }

    /** Record a manual/offline payment against an invoice. */
    public function payOffline(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,cheque',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:255',
        ]);
        $schoolId = $this->getSchoolId();

        $invoice = DB::table('fee_invoices')->where('id', $id)
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))->first();
        if (!$invoice) return back()->withErrors(['error' => 'Invoice not found']);
        if ($invoice->status === 'paid') return back()->withErrors(['error' => 'Invoice already paid']);

        DB::table('payment_transactions')->insert([
            'school_id' => $invoice->school_id,
            'payable_type' => 'fee',
            'fee_invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'parent_user_id' => auth()->id(),
            'amount' => $invoice->total_amount,
            'payment_method' => $validated['payment_method'],
            'gateway' => 'offline',
            'status' => 'success',
            'platform_commission' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('fee_invoices')->where('id', $invoice->id)->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Offline payment recorded.');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Per-student fee override (increase/decrease)
    // ═══════════════════════════════════════════════════════════════════

    public function setStudentOverride(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'fee_category_id' => 'required|integer|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);
        $schoolId = $this->getSchoolId() ?? DB::table('students')->where('id', $validated['student_id'])->value('school_id');

        DB::table('student_fee_overrides')->updateOrInsert(
            [
                'school_id' => $schoolId,
                'student_id' => $validated['student_id'],
                'fee_category_id' => $validated['fee_category_id'],
            ],
            [
                'amount' => $validated['amount'],
                'reason' => $validated['reason'] ?? null,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Student fee updated.');
    }
}
