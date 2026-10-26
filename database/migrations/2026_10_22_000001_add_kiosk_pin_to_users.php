<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional per-staff PIN for the shared "kiosk" attendance device. When set,
 * the staff member must enter it on the kiosk after picking their name, so a
 * colleague can't mark attendance for them. Null = no PIN required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kiosk_pin')) {
                $table->string('kiosk_pin', 10)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kiosk_pin')) {
                $table->dropColumn('kiosk_pin');
            }
        });
    }
};
