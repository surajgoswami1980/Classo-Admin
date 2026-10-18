<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student submissions against an assignment, plus grading.
 *
 * The table may already exist from an earlier build with a slightly different
 * shape (submission_text/grade). This migration creates it if missing and,
 * either way, reconciles the columns the new flow needs so both old and new
 * installs converge on the same schema:
 *   description, attachment_type, status, marks, user_id, timestamps.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assignment_submissions')) {
            Schema::create('assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_id');
                $table->unsignedBigInteger('assignment_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('description')->nullable();
                $table->string('attachment_url', 600)->nullable();
                $table->string('attachment_type', 30)->nullable();
                $table->enum('status', ['pending', 'submitted', 'graded', 'missed'])->default('pending');
                $table->timestamp('submitted_at')->nullable();
                $table->boolean('is_late')->default(false);
                $table->decimal('marks', 6, 2)->nullable();
                $table->text('feedback')->nullable();
                $table->unsignedBigInteger('graded_by')->nullable();
                $table->timestamp('graded_at')->nullable();
                $table->timestamps();

                $table->unique(['assignment_id', 'student_id'], 'uniq_assignment_student');
                $table->index(['school_id', 'assignment_id'], 'idx_sub_assignment');
                $table->index(['student_id'], 'idx_sub_student');
            });
            return;
        }

        // Reconcile existing table.
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_submissions', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('assignment_submissions', 'description')) {
                $table->text('description')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('assignment_submissions', 'attachment_type')) {
                $table->string('attachment_type', 30)->nullable()->after('attachment_url');
            }
            if (!Schema::hasColumn('assignment_submissions', 'status')) {
                $table->enum('status', ['pending', 'submitted', 'graded', 'missed'])->default('pending')->after('attachment_type');
            }
            if (!Schema::hasColumn('assignment_submissions', 'marks')) {
                $table->decimal('marks', 6, 2)->nullable()->after('is_late');
            }
            if (!Schema::hasColumn('assignment_submissions', 'created_at')) {
                $table->timestamps();
            }
        });

        // Backfill: copy legacy submission_text -> description, grade -> marks.
        if (Schema::hasColumn('assignment_submissions', 'submission_text')) {
            \DB::statement('UPDATE assignment_submissions SET description = submission_text WHERE description IS NULL AND submission_text IS NOT NULL');
        }
        if (Schema::hasColumn('assignment_submissions', 'grade')) {
            \DB::statement('UPDATE assignment_submissions SET marks = grade WHERE marks IS NULL AND grade IS NOT NULL');
        }
        // Set status from existing data.
        \DB::statement("UPDATE assignment_submissions SET status = 'graded' WHERE status = 'pending' AND marks IS NOT NULL");
        \DB::statement("UPDATE assignment_submissions SET status = 'submitted' WHERE status = 'pending' AND submitted_at IS NOT NULL AND marks IS NULL");
    }

    public function down(): void
    {
        // Non-destructive: leave the table in place.
    }
};
