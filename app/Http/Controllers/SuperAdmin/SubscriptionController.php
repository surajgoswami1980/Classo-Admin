<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Super-admin subscription management.
 *
 * In this data model a "subscription" is the plan attached to each School
 * (subscription_plan / subscription_start / subscription_end / max_students /
 * max_staff / is_active). This controller manages those fields per school and
 * reports platform revenue derived from the payment_transactions table
 * (platform_commission column) when it is available.
 */
class SubscriptionController extends Controller
{
    /** Available subscription plans and their default limits. */
    public const PLANS = [
        'trial'      => ['label' => 'Trial',      'max_students' => 300,   'max_staff' => 30,  'price' => 0],
        'basic'      => ['label' => 'Basic',      'max_students' => 300,   'max_staff' => 30,  'price' => 4999],
        'standard'   => ['label' => 'Standard',   'max_students' => 1000,  'max_staff' => 80,  'price' => 9999],
        'premium'    => ['label' => 'Premium',    'max_students' => 3000,  'max_staff' => 200, 'price' => 19999],
        'enterprise' => ['label' => 'Enterprise', 'max_students' => 99999, 'max_staff' => 999, 'price' => 39999],
    ];

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'plan', 'status']);

        $subscriptions = School::query()
            ->when(!empty($filters['search']), fn ($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('name', 'like', "%{$filters['search']}%")
                   ->orWhere('code', 'like', "%{$filters['search']}%");
            }))
            ->when(!empty($filters['plan']), fn ($q) => $q->where('subscription_plan', $filters['plan']))
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                if ($filters['status'] === 'expired') {
                    return $q->whereNotNull('subscription_end')->where('subscription_end', '<', now());
                }
                return $q->where('is_active', $filters['status'] === 'active');
            })
            ->latest()
            ->paginate(20)
            ->appends($filters);

        $plans = self::PLANS;

        return view('super-admin.subscriptions.index', compact('subscriptions', 'filters', 'plans'));
    }

    public function create()
    {
        $plans = self::PLANS;
        $schools = School::orderBy('name')->get(['id', 'name', 'code', 'subscription_plan']);

        return view('super-admin.subscriptions.create', compact('plans', 'schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'          => 'required|exists:schools,id',
            'subscription_plan'  => 'required|string|in:' . implode(',', array_keys(self::PLANS)),
            'subscription_start' => 'required|date',
            'subscription_end'   => 'required|date|after:subscription_start',
            'max_students'       => 'nullable|integer|min:10|max:99999',
            'max_staff'          => 'nullable|integer|min:1|max:9999',
        ]);

        $plan = self::PLANS[$validated['subscription_plan']];

        $school = School::findOrFail($validated['school_id']);
        $school->update([
            'subscription_plan'  => $validated['subscription_plan'],
            'subscription_start' => $validated['subscription_start'],
            'subscription_end'   => $validated['subscription_end'],
            'max_students'       => $validated['max_students'] ?? $plan['max_students'],
            'max_staff'          => $validated['max_staff'] ?? $plan['max_staff'],
            'is_active'          => true,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for '{$school->name}' set to {$plan['label']}.");
    }

    public function show(School $subscription)
    {
        $school = $subscription;
        $plans = self::PLANS;
        $revenue = $this->schoolRevenue($school->id);

        return view('super-admin.subscriptions.show', compact('school', 'plans', 'revenue'));
    }

    public function edit(School $subscription)
    {
        $school = $subscription;
        $plans = self::PLANS;

        return view('super-admin.subscriptions.edit', compact('school', 'plans'));
    }

    public function update(Request $request, School $subscription)
    {
        $validated = $request->validate([
            'subscription_plan'  => 'required|string|in:' . implode(',', array_keys(self::PLANS)),
            'subscription_start' => 'required|date',
            'subscription_end'   => 'required|date|after:subscription_start',
            'max_students'       => 'nullable|integer|min:10|max:99999',
            'max_staff'          => 'nullable|integer|min:1|max:9999',
            'is_active'          => 'nullable|boolean',
        ]);

        $plan = self::PLANS[$validated['subscription_plan']];

        $subscription->update([
            'subscription_plan'  => $validated['subscription_plan'],
            'subscription_start' => $validated['subscription_start'],
            'subscription_end'   => $validated['subscription_end'],
            'max_students'       => $validated['max_students'] ?? $plan['max_students'],
            'max_staff'          => $validated['max_staff'] ?? $plan['max_staff'],
            'is_active'          => $request->boolean('is_active', $subscription->is_active),
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for '{$subscription->name}' updated.");
    }

    public function destroy(School $subscription)
    {
        // Cancelling a subscription deactivates the school rather than deleting data.
        $subscription->update(['is_active' => false]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for '{$subscription->name}' cancelled (school deactivated).");
    }

    public function revenue(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date']);
        $from = !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->startOfDay() : now()->startOfYear();
        $to = !empty($filters['to_date']) ? Carbon::parse($filters['to_date'])->endOfDay() : now()->endOfDay();

        $stats = ['total_revenue' => 0, 'monthly_revenue' => 0, 'commission' => 0];
        $revenueByPlan = [];
        $monthlyTrend = [];

        if (Schema::hasTable('payment_transactions')) {
            $base = DB::table('payment_transactions')->where('status', 'success');

            $stats['total_revenue'] = (float) (clone $base)
                ->whereBetween('created_at', [$from, $to])->sum('amount');

            $stats['monthly_revenue'] = (float) (clone $base)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');

            $stats['commission'] = (float) (clone $base)
                ->whereBetween('created_at', [$from, $to])->sum('platform_commission');

            // Revenue grouped by the paying school's subscription plan.
            $revenueByPlan = (clone $base)
                ->join('schools', 'schools.id', '=', 'payment_transactions.school_id')
                ->whereBetween('payment_transactions.created_at', [$from, $to])
                ->groupBy('schools.subscription_plan')
                ->selectRaw('schools.subscription_plan as plan, SUM(payment_transactions.amount) as total')
                ->pluck('total', 'plan')
                ->toArray();

            // Monthly trend for the last 6 months.
            $monthlyTrend = (clone $base)
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();
        }

        return view('super-admin.subscriptions.revenue', compact('stats', 'revenueByPlan', 'monthlyTrend', 'filters'));
    }

    public function exportRevenue(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date']);
        $from = !empty($filters['from_date']) ? Carbon::parse($filters['from_date'])->startOfDay() : now()->startOfYear();
        $to = !empty($filters['to_date']) ? Carbon::parse($filters['to_date'])->endOfDay() : now()->endOfDay();

        $rows = [];
        if (Schema::hasTable('payment_transactions')) {
            $rows = DB::table('payment_transactions as pt')
                ->join('schools', 'schools.id', '=', 'pt.school_id')
                ->where('pt.status', 'success')
                ->whereBetween('pt.created_at', [$from, $to])
                ->orderBy('pt.created_at')
                ->get([
                    'pt.created_at',
                    'schools.name as school_name',
                    'schools.code as school_code',
                    'schools.subscription_plan as plan',
                    'pt.amount',
                    'pt.platform_commission',
                    'pt.gateway',
                    'pt.payment_method',
                ]);
        }

        $filename = 'revenue_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'School', 'Code', 'Plan', 'Amount', 'Platform Commission', 'Gateway', 'Method']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at,
                    $r->school_name,
                    $r->school_code,
                    $r->plan,
                    $r->amount,
                    $r->platform_commission,
                    $r->gateway,
                    $r->payment_method,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Aggregate successful fee revenue + platform commission for one school. */
    private function schoolRevenue(int $schoolId): array
    {
        if (!Schema::hasTable('payment_transactions')) {
            return ['total' => 0, 'commission' => 0, 'transactions' => 0];
        }

        $base = DB::table('payment_transactions')->where('school_id', $schoolId)->where('status', 'success');

        return [
            'total'        => (float) (clone $base)->sum('amount'),
            'commission'   => (float) (clone $base)->sum('platform_commission'),
            'transactions' => (int) (clone $base)->count(),
        ];
    }
}
