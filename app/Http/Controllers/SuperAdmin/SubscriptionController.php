<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = School::latest()->paginate(20);
        $filters = [];
        return view('super-admin.subscriptions.index', compact('subscriptions', 'filters'));
    }

    public function create()
    {
        return view('super-admin.subscriptions.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('super-admin.subscriptions.index')->with('success', 'Plan created');
    }

    public function show($id)
    {
        return redirect()->route('super-admin.subscriptions.index');
    }

    public function edit($id)
    {
        return redirect()->route('super-admin.subscriptions.index');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('super-admin.subscriptions.index');
    }

    public function destroy($id)
    {
        return redirect()->route('super-admin.subscriptions.index');
    }

    public function revenue(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date']);
        $stats = [
            'total_revenue' => 0,
            'monthly_revenue' => 0,
            'commission' => 0,
        ];
        $revenueByPlan = [];
        $monthlyTrend = [];

        return view('super-admin.subscriptions.revenue', compact('stats', 'revenueByPlan', 'monthlyTrend', 'filters'));
    }

    public function exportRevenue(Request $request)
    {
        return response()->json(['message' => 'Revenue report export started.']);
    }
}
