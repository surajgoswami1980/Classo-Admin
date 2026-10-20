<?php

namespace App\Support;

use App\Models\Enquiry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Sidebar navigation for the admin panel. Built once per request and shared
 * by the sidebar, the header breadcrumb and the "Go to page" search.
 *
 * Every route listed already exists. Compared with the previous sidebar this
 * adds four modules that had routes but no menu entry: Timetable, Hostel,
 * Inventory and Payroll. Routes that aren't registered are dropped silently.
 */
class AdminNavigation
{
    public static function build(): array
    {
        $user = auth()->user();
        $isSuper = $user->hasRole('super-admin');
        $prefix = $isSuper ? 'admin' : 'user';

        $item = fn ($name, $route, $icon, $extra = []) => array_merge(['name' => $name, 'route' => $route, 'icon' => $icon], $extra);

        $groups = [];

        $overview = [$item('Dashboard', $prefix . '.dashboard', 'home')];
        if ($isSuper) {
            $enquiryBadge = Schema::hasTable('enquiries') ? Enquiry::where('status', 'new')->count() : 0;
            $overview[] = $item('Schools', 'admin.schools.index', 'building');
            $overview[] = $item('Subscriptions', 'admin.subscriptions.index', 'briefcase');
            $overview[] = $item('Revenue', 'admin.revenue', 'chart-bar');
            $overview[] = $item('Payment Links', 'admin.payment-links.index', 'link');
            $overview[] = $item('Enquiries', 'admin.enquiries.index', 'inbox', ['badge' => $enquiryBadge]);
        }
        $groups[] = ['label' => $isSuper ? 'Platform' : 'Overview', 'items' => $overview];

        $groups[] = ['label' => 'Academics', 'items' => [
            $item('Sessions', $prefix . '.academic.sessions', 'calendar'),
            $item('Classes', $prefix . '.academic.classes', 'academic-cap'),
            $item('Sections', $prefix . '.academic.sections', 'squares'),
            $item('Students', $prefix . '.students.index', 'users'),
            $item('Teachers', $prefix . '.teachers.index', 'user-badge'),
            $item('Timetable', $prefix . '.timetable.index', 'clock'),
            $item('Assignments', $prefix . '.assignments.index', 'pencil-square'),
            $item('Exams', $prefix . '.exams.index', 'document-text'),
        ]];

        $groups[] = ['label' => 'Attendance', 'items' => [
            $item('Attendance', $prefix . '.attendance.index', 'clipboard-check'),
            $item('Holidays', $prefix . '.holidays.index', 'sun'),
            $item('Attendance Settings', $prefix . '.attendance.settings', 'adjustments'),
        ]];

        $groups[] = ['label' => 'Finance', 'items' => [
            $item('Fees', $prefix . '.fees.index', 'currency-rupee'),
            $item('Fee Categories', $prefix . '.fees.categories', 'tag'),
            $item('Pending Fees', $prefix . '.fees.pending', 'exclamation'),
            $item('Payroll', $prefix . '.payroll.index', 'banknotes'),
        ]];

        $groups[] = ['label' => 'Operations', 'items' => [
            $item('Transport', $prefix . '.transport.index', 'truck'),
            $item('Library', $prefix . '.library.index', 'book-open'),
            $item('Hostel', $prefix . '.hostel.index', 'home-modern'),
            $item('Inventory', $prefix . '.inventory.index', 'cube'),
        ]];

        $groups[] = ['label' => 'Engagement', 'items' => [
            $item('Events', $prefix . '.events.index', 'ticket'),
            $item('Banners', $prefix . '.banners.index', 'photo'),
            $item('Store', $prefix . '.store.index', 'shopping-bag'),
            $item('Notifications', $prefix . '.notifications.index', 'bell'),
        ]];

        $adminItems = [];
        if ($isSuper || $user->hasRole('school-admin') || has_resource_permission('team.view')) {
            $adminItems[] = $item('Team', $prefix . '.team.index', 'user-group');
        }
        if ($isSuper) {
            $adminItems[] = $item('Integrations', 'admin.integrations.index', 'puzzle');
            $adminItems[] = $item('Subscription Billing', 'admin.subscription-invoices.index', 'receipt');
            $adminItems[] = $item('Resources & Permissions', 'admin.resources.index', 'shield');
        } else {
            $adminItems[] = $item('My Subscription', $prefix . '.subscription', 'credit-card');
        }
        $groups[] = ['label' => 'Administration', 'items' => $adminItems];

        // Resolve URLs and pick the single most specific active item:
        //   exact route name > "route.*" child > "section.*" for *.index items.
        // So fees.categories highlights "Fee Categories", fees.invoices highlights "Fees",
        // and attendance.settings.attempts highlights "Attendance Settings".
        $best = ['score' => 0, 'route' => null];
        foreach ($groups as $gi => $g) {
            $groups[$gi]['items'] = array_values(array_filter($g['items'], fn ($i) => Route::has($i['route'])));
            foreach ($groups[$gi]['items'] as $ii => $i) {
                $groups[$gi]['items'][$ii]['url'] = route($i['route']);
                $base = preg_replace('/\.index$/', '', $i['route']);
                $score = request()->routeIs($i['route']) ? 1000
                    : (request()->routeIs($i['route'] . '.*') ? strlen($i['route'])
                    : (str_ends_with($i['route'], '.index') && request()->routeIs($base . '.*') ? strlen($base) : 0));
                if ($score > $best['score']) {
                    $best = ['score' => $score, 'route' => $i['route']];
                }
            }
        }
        $activeGroup = null;
        $activeItem = null;
        foreach ($groups as $gi => $g) {
            foreach ($g['items'] as $ii => $i) {
                $on = $best['route'] !== null && $i['route'] === $best['route'];
                $groups[$gi]['items'][$ii]['active'] = $on;
                if ($on) {
                    $activeGroup = $groups[$gi];
                    $activeItem = $groups[$gi]['items'][$ii];
                }
            }
        }
        $groups = array_values(array_filter($groups, fn ($g) => count($g['items']) > 0));

        $search = collect($groups)
            ->flatMap(fn ($g) => collect($g['items'])->map(fn ($i) => ['name' => $i['name'], 'group' => $g['label'], 'url' => $i['url']]))
            ->values()->all();

        return [
            'isSuper' => $isSuper,
            'prefix' => $prefix,
            'navGroups' => $groups,
            'activeGroup' => $activeGroup,
            'activeItem' => $activeItem,
            'navSearch' => $search,
        ];
    }
}
