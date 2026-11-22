<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school negotiated subscription price. NULL means "use the plan's list
 * price" (SubscriptionController::PLANS). Lets the super admin agree a
 * discounted or custom amount (e.g. ₹3,599 instead of ₹4,999) per school.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('schools', 'subscription_price')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->decimal('subscription_price', 10, 2)->nullable()->after('subscription_plan');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('schools', 'subscription_price')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('subscription_price');
        });
    }
};
