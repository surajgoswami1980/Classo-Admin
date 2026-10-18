<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

/**
 * Super-admin leads inbox. Enquiries are captured from the public marketing
 * website and land here for the sales/onboarding team to work through.
 */
class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status']);

        $enquiries = Enquiry::query()
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->latest()
            ->paginate(15)
            ->appends($filters);

        $stats = [
            'total'     => Enquiry::count(),
            'new'       => Enquiry::where('status', 'new')->count(),
            'contacted' => Enquiry::where('status', 'contacted')->count(),
            'converted' => Enquiry::where('status', 'converted')->count(),
        ];

        $statuses = Enquiry::STATUSES;

        return view('super-admin.enquiries.index', compact('enquiries', 'filters', 'stats', 'statuses'));
    }

    public function show(Enquiry $enquiry)
    {
        return view('super-admin.enquiries.show', compact('enquiry'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'status'      => 'required|in:' . implode(',', Enquiry::STATUSES),
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $enquiry->status = $validated['status'];
        $enquiry->admin_notes = $validated['admin_notes'] ?? $enquiry->admin_notes;
        $enquiry->handled_by = auth()->id();

        // Stamp the first time it leaves "new".
        if ($validated['status'] !== 'new' && !$enquiry->contacted_at) {
            $enquiry->contacted_at = now();
        }

        $enquiry->save();

        return back()->with('success', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted.');
    }
}
