<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The stock Laravel users migration left `email` NOT NULL, but this ERP's
 * design explicitly allows login via phone or employee_id — most students
 * don't have an email address. A NOT NULL email blocks student/teacher
 * creation from both the admin panel and the API whenever email is omitted.
 * The unique index is preserved; MySQL allows multiple NULLs under a
 * UNIQUE index, so this doesn't weaken uniqueness for users who do have one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
