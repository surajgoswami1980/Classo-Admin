<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an optional student_id to library_book_issues so a book issued to a
 * student can be linked directly to the student record (in addition to the
 * existing issued_to_user_id, which stays for staff issues and login lookup).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('library_book_issues', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('issued_to_user_id');
                $table->index(['school_id', 'student_id'], 'idx_school_student');
            }
        });
    }

    public function down(): void
    {
        Schema::table('library_book_issues', function (Blueprint $table) {
            if (Schema::hasColumn('library_book_issues', 'student_id')) {
                $table->dropIndex('idx_school_student');
                $table->dropColumn('student_id');
            }
        });
    }
};
