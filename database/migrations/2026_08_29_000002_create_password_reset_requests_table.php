<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel's stock `password_reset_tokens` table is keyed by `email`
 * (primary key), which doesn't work for this platform's multi-identifier
 * login (phone/employee_id, not just email) or for users who have no
 * email at all (students, most commonly). This table is keyed by
 * user_id instead, and the token itself is stored hashed (like a
 * password) so a DB read alone can't be used to complete a reset.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};
