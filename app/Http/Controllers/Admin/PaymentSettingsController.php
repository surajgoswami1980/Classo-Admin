<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Super-admin configures each school's payment gateway (Razorpay or Cashfree)
 * with that school's OWN keys, so collected money routes to the school's
 * account. Stored in payment_settings; the NestJS API reads it per request.
 */
class PaymentSettingsController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::orderBy('name')->get(['id', 'name', 'code']);
        $settings = DB::table('payment_settings')->get()->keyBy('school_id');

        $selectedId = (int) ($request->get('school') ?: ($schools->first()->id ?? 0));
        $selected = $schools->firstWhere('id', $selectedId) ?? $schools->first();
        $current = $selected ? ($settings[$selected->id] ?? null) : null;

        return view('admin.payment-settings.index', compact('schools', 'settings', 'selected', 'current'));
    }

    public function update(Request $request, $schoolId)
    {
        $v = $request->validate([
            'active_gateway' => 'required|in:none,razorpay,cashfree',
            'razorpay_key_id' => 'nullable|string|max:120',
            'razorpay_key_secret' => 'nullable|string|max:200',
            'razorpay_enabled' => 'nullable|boolean',
            'cashfree_app_id' => 'nullable|string|max:120',
            'cashfree_secret_key' => 'nullable|string|max:200',
            'cashfree_env' => 'nullable|in:TEST,PROD',
            'cashfree_enabled' => 'nullable|boolean',
        ]);

        $data = [
            'active_gateway' => $v['active_gateway'],
            'razorpay_key_id' => $v['razorpay_key_id'] ?? null,
            'razorpay_key_secret' => $v['razorpay_key_secret'] ?? null,
            'razorpay_enabled' => $request->boolean('razorpay_enabled'),
            'cashfree_app_id' => $v['cashfree_app_id'] ?? null,
            'cashfree_secret_key' => $v['cashfree_secret_key'] ?? null,
            'cashfree_env' => $v['cashfree_env'] ?? 'TEST',
            'cashfree_enabled' => $request->boolean('cashfree_enabled'),
            'updated_at' => now(),
        ];

        $exists = DB::table('payment_settings')->where('school_id', $schoolId)->exists();
        if ($exists) {
            DB::table('payment_settings')->where('school_id', $schoolId)->update($data);
        } else {
            $data['school_id'] = $schoolId;
            $data['created_at'] = now();
            DB::table('payment_settings')->insert($data);
        }

        return redirect()->route('admin.payment-settings.index', ['school' => $schoolId])
            ->with('success', 'Payment settings saved for this school.');
    }
}
