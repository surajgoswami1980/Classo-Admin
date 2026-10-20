<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Global platform credentials managed by the super admin (Integrations page):
 * payment gateways (Razorpay / Cashfree), Firebase push, email (SMTP) and SMS.
 *
 * Read by BOTH the Laravel admin and the NestJS API. Secret values are
 * encrypted with AES-256-GCM using SETTINGS_ENCRYPTION_KEY (same key in both
 * .env files). Format: v1:<iv b64>:<tag b64>:<ciphertext b64>
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 40);      // payment | razorpay | cashfree | firebase | email | sms
            $table->string('setting_key', 60);
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['group_name', 'setting_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
