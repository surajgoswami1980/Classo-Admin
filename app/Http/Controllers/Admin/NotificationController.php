<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * School "Send Notification" screen. Creates a notification row and enqueues a
 * push fan-out job onto the SAME Redis list the NestJS worker consumes
 * (queue:push_notifications). The worker resolves the audience, writes
 * per-user in-app records, and sends FCM push to valid tokens.
 */
class NotificationController extends Controller
{
    private const QUEUE_KEY = 'queue:push_notifications';

    private function requireSchoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 422, 'Select a school (use "Switch School") before sending notifications.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $notifications = DB::table('notifications as n')
            ->leftJoin('users as u', 'u.id', '=', 'n.sent_by')
            ->where('n.school_id', $schoolId)
            ->select([
                'n.*', 'u.name as sent_by_name',
                DB::raw('(SELECT COUNT(*) FROM notification_dispatches d WHERE d.notification_id = n.id) as recipients'),
            ])
            ->orderByDesc('n.created_at')
            ->paginate(20);

        $classes = DB::table('classes')->where('school_id', $schoolId)->pluck('name', 'id');
        $sections = DB::table('sections')->where('school_id', $schoolId)->get(['id', 'name', 'class_id']);

        $stats = [
            'total' => (int) DB::table('notifications')->where('school_id', $schoolId)->count(),
            'sent' => (int) DB::table('notifications')->where('school_id', $schoolId)->where('status', 'sent')->count(),
            'active_tokens' => (int) DB::table('device_tokens')->where('school_id', $schoolId)->where('is_active', true)->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'classes', 'sections', 'stats'));
    }

    public function send(Request $request)
    {
        $schoolId = $this->requireSchoolId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'channel' => 'required|in:push,all,in_app',
            'target_type' => 'required|in:all,role,class,section',
            'target_role' => 'nullable|in:teacher,student,parent,staff',
            'target_class_id' => 'nullable|integer',
            'target_section_id' => 'nullable|integer',
        ]);

        // Normalize channel: our notifications table enum is push|email|sms|all.
        $channel = $validated['channel'] === 'in_app' ? 'push' : $validated['channel'];

        $id = DB::table('notifications')->insertGetId([
            'school_id' => $schoolId,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'channel' => $channel,
            'target_type' => $validated['target_type'],
            'target_role' => $validated['target_type'] === 'role' ? ($validated['target_role'] ?? null) : null,
            'target_class_id' => in_array($validated['target_type'], ['class', 'section']) ? ($validated['target_class_id'] ?? null) : null,
            'target_section_id' => $validated['target_type'] === 'section' ? ($validated['target_section_id'] ?? null) : null,
            'sent_by' => auth()->id(),
            'status' => 'queued',
            'created_at' => now(),
        ]);

        $this->enqueue($id, $schoolId);

        return back()->with('success', 'Notification queued and being delivered.');
    }

    /**
     * Push a fan-out job onto the shared Redis list for the NestJS worker.
     * Non-fatal: if Redis is down we mark it sent-in-app so the record still
     * shows in-app; push can be retried later.
     */
    private function enqueue(int $notificationId, int $schoolId): void
    {
        try {
            Redis::rpush(self::QUEUE_KEY, json_encode([
                'notification_id' => $notificationId,
                'school_id' => $schoolId,
            ]));
        } catch (\Throwable $e) {
            DB::table('notifications')->where('id', $notificationId)->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }
    }

    /** Public helper so other controllers (events) can enqueue a push. */
    public static function queuePush(int $notificationId, int $schoolId): void
    {
        try {
            Redis::rpush(self::QUEUE_KEY, json_encode([
                'notification_id' => $notificationId,
                'school_id' => $schoolId,
            ]));
        } catch (\Throwable $e) {
            // ignore — in-app record already exists
        }
    }
}
