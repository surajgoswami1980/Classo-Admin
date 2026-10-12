<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Banner management for a school: slider (carousel, many active) and popup
 * (modal, max ONE active per school). Banners can link to an event or a URL,
 * and can be targeted at all / a class / a section. Images are uploaded to
 * the public disk; a thumbnail may be supplied or reused from the image.
 */
class BannerController extends Controller
{
    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before managing banners.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $banners = Banner::forSchool($schoolId)
            ->orderBy('placement')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $events = DB::table('events')
            ->where('school_id', $schoolId)
            ->whereIn('status', ['published', 'draft'])
            ->orderByDesc('start_at')
            ->get(['id', 'title', 'start_at', 'status']);

        $classes = DB::table('classes')->where('school_id', $schoolId)->pluck('name', 'id');
        $sections = DB::table('sections')->where('school_id', $schoolId)->get(['id', 'name', 'class_id']);

        $stats = [
            'sliders' => $banners->where('placement', 'slider')->where('is_active', true)->count(),
            'popup_active' => $banners->where('placement', 'popup')->where('is_active', true)->count(),
            'total' => $banners->count(),
        ];

        return view('admin.banners.index', compact('banners', 'events', 'classes', 'sections', 'stats'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        // Image is required when creating a new banner — validate up front so
        // the user gets a friendly inline error instead of a hard 422 page.
        $request->validate(
            ['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096'],
            ['image.required' => 'Please choose a banner image.'],
        );

        $data = $this->validateBanner($request);

        $data['image'] = $this->storeUpload($request, 'image', $schoolId);
        $data['thumbnail'] = $this->storeUpload($request, 'thumbnail', $schoolId) ?? $data['image'];

        // Enforce: only one active popup per school.
        if ($data['placement'] === 'popup' && $data['is_active']) {
            Banner::forSchool($schoolId)->where('placement', 'popup')->update(['is_active' => false]);
        }

        $data['school_id'] = $schoolId;
        $data['created_by'] = auth()->id();
        Banner::create($data);

        return back()->with('success', 'Banner added.');
    }

    public function update(Request $request, $id)
    {
        $schoolId = $this->requireSchoolId();
        $banner = Banner::forSchool($schoolId)->findOrFail($id);
        $data = $this->validateBanner($request);

        $newImage = $this->storeUpload($request, 'image', $schoolId);
        if ($newImage) {
            $this->deleteFile($banner->image);
            $data['image'] = $newImage;
        }
        $newThumb = $this->storeUpload($request, 'thumbnail', $schoolId);
        if ($newThumb) {
            $data['thumbnail'] = $newThumb;
        }

        if ($data['placement'] === 'popup' && $data['is_active']) {
            Banner::forSchool($schoolId)->where('placement', 'popup')->where('id', '!=', $id)->update(['is_active' => false]);
        }

        $banner->update($data);

        return back()->with('success', 'Banner updated.');
    }

    public function destroy($id)
    {
        $schoolId = $this->requireSchoolId();
        $banner = Banner::forSchool($schoolId)->findOrFail($id);
        $this->deleteFile($banner->image);
        if ($banner->thumbnail && $banner->thumbnail !== $banner->image) {
            $this->deleteFile($banner->thumbnail);
        }
        $banner->delete();

        return back()->with('success', 'Banner deleted.');
    }

    /**
     * Toggle active. For popup, activating one deactivates the others.
     */
    public function toggle($id)
    {
        $schoolId = $this->requireSchoolId();
        $banner = Banner::forSchool($schoolId)->findOrFail($id);
        $next = !$banner->is_active;

        if ($banner->placement === 'popup' && $next) {
            Banner::forSchool($schoolId)->where('placement', 'popup')->update(['is_active' => false]);
        }
        $banner->update(['is_active' => $next]);

        return back()->with('success', 'Banner ' . ($next ? 'activated' : 'deactivated') . '.');
    }

    /**
     * Reorder sliders (expects ordered array of banner ids).
     */
    public function reorder(Request $request)
    {
        $schoolId = $this->requireSchoolId();
        $ids = $request->input('order', []);
        foreach ($ids as $i => $bannerId) {
            Banner::forSchool($schoolId)->where('id', $bannerId)->update(['sort_order' => $i]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function validateBanner(Request $request): array
    {
        $v = $request->validate([
            'title' => 'nullable|string|max:200',
            'placement' => 'required|in:slider,popup',
            'link_type' => 'required|in:none,event,url',
            'event_id' => 'nullable|integer',
            'link_url' => 'nullable|string|max:500',
            'audience_type' => 'required|in:all,class,section',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        return [
            'title' => $v['title'] ?? null,
            'placement' => $v['placement'],
            'link_type' => $v['link_type'],
            'event_id' => $v['link_type'] === 'event' ? ($v['event_id'] ?? null) : null,
            'link_url' => $v['link_type'] === 'url' ? ($v['link_url'] ?? null) : null,
            'audience_type' => $v['audience_type'],
            'class_id' => $v['audience_type'] === 'all' ? null : ($v['class_id'] ?? null),
            'section_id' => $v['audience_type'] === 'section' ? ($v['section_id'] ?? null) : null,
            'sort_order' => $v['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'start_date' => $v['start_date'] ?? null,
            'end_date' => $v['end_date'] ?? null,
        ];
    }

    private function storeUpload(Request $request, string $field, int $schoolId): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }
        return $request->file($field)->store("banners/{$schoolId}", 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && !str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
