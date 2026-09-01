<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Timetable
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_number');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'class_id', 'section_id', 'day_of_week'], 'idx_school_class_day');
            $table->index(['school_id', 'teacher_id', 'day_of_week'], 'idx_school_teacher_day');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        // Assignments / Homework
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('attachment_url', 500)->nullable();
            $table->string('attachment_type', 50)->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->integer('max_marks')->default(100);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'class_id', 'section_id'], 'idx_school_class');
            $table->index(['school_id', 'teacher_id'], 'idx_school_teacher');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('student_id');
            $table->text('submission_text')->nullable();
            $table->string('attachment_url', 500)->nullable();
            $table->tinyInteger('is_late')->default(0);
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('grade', 10)->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedBigInteger('graded_by')->nullable();
            $table->timestamp('graded_at')->nullable();

            $table->unique(['assignment_id', 'student_id'], 'uk_assignment_student');
            $table->index(['school_id', 'assignment_id'], 'idx_assignment');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title', 255);
            $table->text('body');
            $table->enum('channel', ['push', 'email', 'sms', 'all'])->default('all');
            $table->enum('target_type', ['all', 'role', 'class', 'section', 'individual']);
            $table->enum('target_role', ['teacher', 'student', 'parent', 'staff'])->nullable();
            $table->unsignedBigInteger('target_class_id')->nullable();
            $table->unsignedBigInteger('target_section_id')->nullable();
            $table->json('target_user_ids')->nullable();
            $table->unsignedBigInteger('sent_by');
            $table->enum('status', ['queued', 'processing', 'sent', 'failed'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('timetable_periods');
    }
};
