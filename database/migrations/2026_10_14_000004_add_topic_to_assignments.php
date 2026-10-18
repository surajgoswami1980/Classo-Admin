<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'topic')) {
                $table->string('topic', 255)->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
