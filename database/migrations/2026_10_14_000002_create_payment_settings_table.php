<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-school (client-wise) payment gateway configuration.
 *
 * Each school can enable EITHER Razorpay OR Cashfree and plug in their OWN
 * gateway keys, so collected fees/store/event money settles into that
 * school's own account (e.g. DAV fees -> DAV's Razorpay account). When a
 * school has no config, the platform falls back to the global keys in the
 * API .env (used for demo and for subscription payments to the platform).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->unique();

            // active_gateway: razorpay | cashfree | none (none = use platform default)
            $table->enum('active_gateway', ['razorpay', 'cashfree', 'none'])->default('none');

            // Razorpay credentials (school's own account)
            $table->string('razorpay_key_id', 120)->nullable();
            $table->string('razorpay_key_secret', 200)->nullable();
            $table->boolean('razorpay_enabled')->default(false);

            // Cashfree credentials (school's own account)
            $table->string('cashfree_app_id', 120)->nullable();
            $table->string('cashfree_secret_key', 200)->nullable();
            $table->enum('cashfree_env', ['TEST', 'PROD'])->default('TEST');
            $table->boolean('cashfree_enabled')->default(false);

            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
