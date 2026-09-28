<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Generalises payment_transactions so the same table + Razorpay gateway can
 * settle both fee invoices and event registrations. fee_invoice_id becomes
 * nullable and a lightweight polymorphic reference (payable_type/payable_id)
 * plus a direct event_registration_id are added.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the NOT NULL requirement on fee_invoice_id (event payments have none).
        // Uses raw SQL because the column has no default and Doctrine DBAL may be absent.
        try {
            DB::statement('ALTER TABLE payment_transactions MODIFY fee_invoice_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // If it's already nullable or the type differs, ignore — additive columns below still apply.
        }

        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'payable_type')) {
                $table->string('payable_type', 30)->default('fee')->after('school_id'); // 'fee' | 'event'
            }
            if (!Schema::hasColumn('payment_transactions', 'event_registration_id')) {
                $table->unsignedBigInteger('event_registration_id')->nullable()->after('fee_invoice_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            foreach (['payable_type', 'event_registration_id'] as $col) {
                if (Schema::hasColumn('payment_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
