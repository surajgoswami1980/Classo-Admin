<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment links the super admin sends to prospects / schools (onboarding fee,
 * setup charges, subscription…). Created on the PLATFORM gateway
 * (Razorpay Payment Links / Cashfree Payment Links) with NO split, so the
 * full amount settles into the platform account.
 *
 * Status is kept in sync by gateway webhooks (handled in the NestJS API)
 * and by the "Refresh" action in the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();              // PL-20261020-AB12C (our id, also sent to the gateway)
            $table->string('purpose', 30)->default('onboarding');    // onboarding | setup | subscription | other
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR');

            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->unsignedBigInteger('enquiry_id')->nullable()->index();
            $table->string('customer_name', 150);
            $table->string('customer_email', 150)->nullable();
            $table->string('customer_phone', 20);

            $table->string('gateway', 20);                            // razorpay | cashfree
            $table->string('gateway_link_id', 100)->nullable()->index();
            $table->string('short_url', 500)->nullable();
            $table->string('status', 20)->default('created')->index(); // created | paid | partially_paid | expired | cancelled | failed
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('gateway_payment_id', 100)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('notified_sms')->default(false);
            $table->boolean('notified_email')->default(false);
            $table->text('notes')->nullable();
            $table->text('last_error')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};
