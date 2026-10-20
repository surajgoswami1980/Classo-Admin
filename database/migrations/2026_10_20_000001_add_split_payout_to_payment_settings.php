<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-gateway split payouts (Razorpay Route / Cashfree Easy Split).
 *
 * All online payments are collected on the PLATFORM gateway configured in the
 * API .env. When a school has split enabled with a linked account / vendor id,
 * the school's share is transferred automatically and the platform keeps its
 * platform fee + route/split fee. Without split data the full amount stays in
 * the platform account and is flagged for manual settlement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_settings', 'split_enabled')) {
                $table->boolean('split_enabled')->default(false)->after('school_id');
            }
            if (!Schema::hasColumn('payment_settings', 'razorpay_linked_account_id')) {
                // Razorpay Route linked account, e.g. acc_XXXXXXXXXXXXXX
                $table->string('razorpay_linked_account_id', 60)->nullable()->after('split_enabled');
            }
            if (!Schema::hasColumn('payment_settings', 'cashfree_vendor_id')) {
                // Cashfree Easy Split vendor id
                $table->string('cashfree_vendor_id', 60)->nullable()->after('razorpay_linked_account_id');
            }
            // NULL = use the platform defaults from the API .env
            if (!Schema::hasColumn('payment_settings', 'platform_fee_percent')) {
                $table->decimal('platform_fee_percent', 5, 2)->nullable()->after('cashfree_vendor_id');
            }
            if (!Schema::hasColumn('payment_settings', 'gateway_fee_percent')) {
                // Route / split fee recovered from the school's share
                $table->decimal('gateway_fee_percent', 5, 2)->nullable()->after('platform_fee_percent');
            }
        });

        // gateway was enum('razorpay','offline') — Cashfree rows could not be stored.
        DB::statement("ALTER TABLE payment_transactions MODIFY gateway VARCHAR(20) NOT NULL DEFAULT 'razorpay'");

        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'gateway_fee')) {
                $table->decimal('gateway_fee', 10, 2)->default(0)->after('platform_commission');
            }
            if (!Schema::hasColumn('payment_transactions', 'school_share')) {
                $table->decimal('school_share', 10, 2)->nullable()->after('gateway_fee');
            }
            if (!Schema::hasColumn('payment_transactions', 'split_status')) {
                // auto split: pending → scheduled → processed | failed ; no split data: manual ; offline: none
                $table->string('split_status', 20)->nullable()->after('school_share');
            }
            if (!Schema::hasColumn('payment_transactions', 'split_account')) {
                $table->string('split_account', 60)->nullable()->after('split_status');
            }
            if (!Schema::hasColumn('payment_transactions', 'split_reference')) {
                $table->string('split_reference', 100)->nullable()->after('split_account');
            }
        });

        Schema::table('store_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('store_orders', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('store_orders', 'gateway_fee')) {
                $table->decimal('gateway_fee', 10, 2)->default(0)->after('platform_fee');
            }
            if (!Schema::hasColumn('store_orders', 'school_share')) {
                $table->decimal('school_share', 10, 2)->nullable()->after('gateway_fee');
            }
            if (!Schema::hasColumn('store_orders', 'split_status')) {
                $table->string('split_status', 20)->nullable()->after('school_share');
            }
            if (!Schema::hasColumn('store_orders', 'split_account')) {
                $table->string('split_account', 60)->nullable()->after('split_status');
            }
            if (!Schema::hasColumn('store_orders', 'split_reference')) {
                $table->string('split_reference', 100)->nullable()->after('split_account');
            }
        });
    }

    public function down(): void
    {
        $drop = function (string $tableName, array $cols) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        };

        $drop('store_orders', ['platform_fee', 'gateway_fee', 'school_share', 'split_status', 'split_account', 'split_reference']);
        $drop('payment_transactions', ['gateway_fee', 'school_share', 'split_status', 'split_account', 'split_reference']);
        $drop('payment_settings', ['split_enabled', 'razorpay_linked_account_id', 'cashfree_vendor_id', 'platform_fee_percent', 'gateway_fee_percent']);
        // gateway column intentionally stays VARCHAR: reverting to the enum would fail on cashfree rows.
    }
};
