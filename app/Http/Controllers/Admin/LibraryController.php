<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Library management for the admin/client panel. Books are added to the
 * catalog, issued/assigned to students (or staff), returned with an overdue
 * fine, and listed with days-to-expire. All queries are scoped to the school
 * in context via current_school_id() (handles super-admin impersonation).
 */
class LibraryController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing the library.');

        return $schoolId;
    }

    private function finePerDay(int $schoolId): float
    {
        $settings = DB::table('schools')->where('id', $schoolId)->value('settings');
        $settings = $settings ? json_decode($settings, true) : [];

        return (float) ($settings['library_fine_per_day'] ?? 2);
    }

    // ─── Books catalog ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();
        $filters = $request->only(['search', 'category']);

        $books = DB::table('library_books')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when(!empty($filters['search']), fn ($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('title', 'like', "%{$filters['search']}%")
                   ->orWhere('author', 'like', "%{$filters['search']}%")
                   ->orWhere('isbn', 'like', "%{$filters['search']}%");
            }))
            ->when(!empty($filters['category']), fn ($q) => $q->where('category', $filters['category']))
            ->orderBy('title')
            ->paginate(20)
            ->appends($filters);

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
            'total_books'   => (int) DB::table('library_books')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->sum('total_copies'),
            'issued'        => (int) DB::table('library_book_issues')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'issued')->count(),
            'overdue'       => (int) DB::table('library_book_issues')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('status', 'issued')->whereDate('due_date', '<', now())->count(),
            'fine_collected' => (float) DB::table('library_book_issues')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->where('fine_paid', 1)->sum('fine_amount'),
        ];

        return view('admin.library.index', compact('books', 'students', 'filters', 'stats'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'total_copies' => 'required|integer|min:1|max:9999',
            'rack_location' => 'nullable|string|max:50',
        ]);

        DB::table('library_books')->insert($validated + [
            'school_id' => $schoolId,
            'available_copies' => $validated['total_copies'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Book added to the catalog.');
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->requireSchoolId();
        $book = DB::table('library_books')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($book, 404, 'Book not found.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'total_copies' => 'required|integer|min:1|max:9999',
            'rack_location' => 'nullable|string|max:50',
        ]);

        $issued = $book->total_copies - $book->available_copies;
        if ($validated['total_copies'] < $issued) {
            return back()->withErrors(['total_copies' => "Cannot reduce total copies below {$issued} (currently issued)."]);
        }

        DB::table('library_books')->where('id', $id)->update($validated + [
            'available_copies' => $book->available_copies + ($validated['total_copies'] - $book->total_copies),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Book updated.');
    }

    public function destroy($id)
    {
        $schoolId = $this->requireSchoolId();
        $book = DB::table('library_books')->where('id', $id)->where('school_id', $schoolId)->first();
        abort_unless($book, 404, 'Book not found.');

        $activeIssues = DB::table('library_book_issues')->where('book_id', $id)->where('status', 'issued')->count();
        if ($activeIssues > 0) {
            return back()->withErrors(['error' => 'Cannot delete a book that is currently issued.']);
        }

        DB::table('library_books')->where('id', $id)->delete();

        return back()->with('success', 'Book removed.');
    }

    // ─── Issue / assign / return ─────────────────────────────────────────

    /**
     * Assign (issue) a book to a student. Resolves the student's linked
     * user_id, stores both ids, and decrements available copies atomically.
     */
    public function assign(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $validated = $request->validate([
            'book_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('library_books', 'id')->where('school_id', $schoolId)],
            'student_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $book = DB::table('library_books')->where('id', $validated['book_id'])->where('school_id', $schoolId)->first();
        if ($book->available_copies <= 0) {
            return back()->withErrors(['error' => 'No copies available for this book.']);
        }

        $userId = DB::table('students')->where('id', $validated['student_id'])->value('user_id');

        DB::transaction(function () use ($schoolId, $validated, $userId) {
            DB::table('library_book_issues')->insert([
                'school_id' => $schoolId,
                'book_id' => $validated['book_id'],
                'issued_to_user_id' => $userId,
                'student_id' => $validated['student_id'],
                'issue_date' => now()->toDateString(),
                'due_date' => $validated['due_date'],
                'status' => 'issued',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('library_books')->where('id', $validated['book_id'])->decrement('available_copies');
        });

        return back()->with('success', 'Book assigned to student.');
    }

    /**
     * Return an issued book — computes the overdue fine (per-day rate from
     * school settings, default ₹2/day) and restores the available copy.
     */
    public function returnBook(Request $request, $issueId)
    {
        $schoolId = $this->requireSchoolId();
        $issue = DB::table('library_book_issues')->where('id', $issueId)->where('school_id', $schoolId)->first();
        abort_unless($issue, 404, 'Issue record not found.');

        if ($issue->status === 'returned') {
            return back()->withErrors(['error' => 'This book has already been returned.']);
        }

        $fine = 0;
        $due = \Carbon\Carbon::parse($issue->due_date)->startOfDay();
        $today = now()->startOfDay();
        if ($today->gt($due)) {
            $fine = $today->diffInDays($due) * $this->finePerDay($schoolId);
        }

        DB::transaction(function () use ($issueId, $issue, $fine) {
            DB::table('library_book_issues')->where('id', $issueId)->update([
                'return_date' => now()->toDateString(),
                'status' => 'returned',
                'fine_amount' => $fine,
                'updated_at' => now(),
            ]);
            DB::table('library_books')->where('id', $issue->book_id)->increment('available_copies');
        });

        return back()->with('success', $fine > 0 ? "Book returned. Overdue fine: ₹{$fine}." : 'Book returned.');
    }

    public function markFinePaid($issueId)
    {
        $schoolId = $this->requireSchoolId();
        $issue = DB::table('library_book_issues')->where('id', $issueId)->where('school_id', $schoolId)->first();
        abort_unless($issue, 404, 'Issue record not found.');

        DB::table('library_book_issues')->where('id', $issueId)->update(['fine_paid' => 1, 'updated_at' => now()]);

        return back()->with('success', 'Fine marked as paid.');
    }

    /**
     * Issued-books register with borrower, fine and days-to-expire.
     */
    public function issues(Request $request)
    {
        $schoolId = $this->schoolId();
        $filters = $request->only(['status', 'filter']);
        $finePerDay = $schoolId ? $this->finePerDay($schoolId) : 2;

        $issues = DB::table('library_book_issues as i')
            ->join('library_books as b', 'b.id', '=', 'i.book_id')
            ->leftJoin('users as u', 'u.id', '=', 'i.issued_to_user_id')
            ->leftJoin('students as s', 's.id', '=', 'i.student_id')
            ->when($schoolId, fn ($q) => $q->where('i.school_id', $schoolId))
            ->when(!empty($filters['status']), fn ($q) => $q->where('i.status', $filters['status']))
            ->when(($filters['filter'] ?? '') === 'overdue', fn ($q) => $q->where('i.status', 'issued')->whereDate('i.due_date', '<', now()))
            ->when(($filters['filter'] ?? '') === 'expiring', fn ($q) => $q->where('i.status', 'issued')->whereBetween('i.due_date', [now()->toDateString(), now()->addDays(7)->toDateString()]))
            ->select([
                'i.*',
                'b.title as book_title', 'b.author as book_author',
                'u.name as borrower_name',
                's.roll_number', 's.admission_number',
                DB::raw('DATEDIFF(i.due_date, CURDATE()) as days_to_expire'),
            ])
            ->orderByRaw("i.status = 'issued' DESC")
            ->orderBy('i.due_date')
            ->paginate(30)
            ->appends($filters);

        // Project the running fine for still-issued overdue records.
        $issues->getCollection()->transform(function ($row) use ($finePerDay) {
            $days = $row->days_to_expire !== null ? (int) $row->days_to_expire : null;
            $row->current_fine = (float) $row->fine_amount;
            if ($row->status === 'issued' && $days !== null && $days < 0) {
                $row->current_fine = abs($days) * $finePerDay;
            }
            $row->is_overdue = $row->status === 'issued' && $days !== null && $days < 0;
            return $row;
        });

        return view('admin.library.issues', compact('issues', 'filters'));
    }
}
