<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscription billing from the PLATFORM to each school.
 *
 * The super-admin raises a subscription invoice / payment request for a
 * school (amount, plan, period). The school-admin sees "Pay Subscription"
 * and pays via the PLATFORM's Razorpay account (the global keys in the API
 * .env) — NOT the school's own gateway. Money settles to the platform.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('invoice_number', 60)->unique();
            $table->string('plan', 40)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('period', 40)->nullable();       // e.g. "Oct 2026" or "Annual 2026-27"
            $table->text('notes')->nullable();

            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->string('gateway', 20)->default('razorpay');
            $table->string('razorpay_order_id', 120)->nullable();
            $table->string('razorpay_payment_id', 120)->nullable();
            $table->string('razorpay_link', 600)->nullable(); // optional shareable link
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // super-admin user id

            $table->timestamps();

            $table->index(['school_id', 'status'], 'idx_subinv_status');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
