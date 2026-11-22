<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * schools.subscription_plan was created as ENUM('basic','standard','premium','enterprise'),
 * but onboarding saves 'trial' and SubscriptionController::PLANS includes it.
 * Strict MySQL rejects the insert ("Data truncated for column 'subscription_plan'").
 *
 * Use a plain VARCHAR(50) instead, matching the NestJS SchoolEntity
 * (`length: 50`), so every plan key in PLANS is accepted. Existing values are
 * kept unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('schools', 'subscription_plan')) {
            return;
        }

        DB::statement("ALTER TABLE schools MODIFY subscription_plan VARCHAR(50) NOT NULL DEFAULT 'trial'");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('schools', 'subscription_plan')) {
            return;
        }

        // Rows on 'trial' cannot be stored in the original ENUM; map them to 'basic' first.
        DB::table('schools')
            ->whereNotIn('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])
            ->update(['subscription_plan' => 'basic']);

        DB::statement("ALTER TABLE schools MODIFY subscription_plan ENUM('basic','standard','premium','enterprise') NOT NULL DEFAULT 'basic'");
    }
};
