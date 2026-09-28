<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds driver & conductor details directly on the vehicle, so a bus/van can
 * carry its own crew independent of a route. (The route also keeps its own
 * optional driver fields for backward compatibility.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'driver_name')) {
                $table->string('driver_name', 100)->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('vehicles', 'driver_phone')) {
                $table->string('driver_phone', 15)->nullable()->after('driver_name');
            }
            if (!Schema::hasColumn('vehicles', 'conductor_name')) {
                $table->string('conductor_name', 100)->nullable()->after('driver_phone');
            }
            if (!Schema::hasColumn('vehicles', 'conductor_phone')) {
                $table->string('conductor_phone', 15)->nullable()->after('conductor_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            foreach (['driver_name', 'driver_phone', 'conductor_name', 'conductor_phone'] as $col) {
                if (Schema::hasColumn('vehicles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
