<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FCM device tokens for push notifications. One row per device per user.
 * A token is unique globally (a device maps to one user at a time); logging
 * in on a new account reassigns it. `is_active` is flipped off when FCM
 * reports the token as unregistered/invalid during a send.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 512);
            $table->string('platform', 20)->default('android'); // android | ios | web
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique('token', 'uk_device_token');
            $table->index(['school_id', 'user_id'], 'idx_token_school_user');
            $table->index(['user_id', 'is_active'], 'idx_token_user_active');
        });

        // Per-recipient dispatch log — lets us show in-app notifications to
        // each targeted user and track push delivery outcome.
        Schema::create('notification_dispatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('push_status', ['pending', 'sent', 'skipped', 'failed'])->default('pending');
            $table->string('failure_reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['notification_id'], 'idx_dispatch_notif');
            $table->index(['user_id'], 'idx_dispatch_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_dispatches');
        Schema::dropIfExists('device_tokens');
    }
};
