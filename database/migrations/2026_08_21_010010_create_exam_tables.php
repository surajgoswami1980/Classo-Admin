<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->string('name', 100);
            $table->enum('exam_type', ['unit_test', 'mid_term', 'final', 'quarterly', 'half_yearly']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('is_published')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'academic_session_id'], 'idx_school_session');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->decimal('max_marks', 5, 2);
            $table->decimal('passing_marks', 5, 2);
            $table->date('exam_date')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'exam_id', 'class_id'], 'idx_exam_class');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });

        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('exam_subject_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('marks_obtained', 5, 2);
            $table->string('grade', 5)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->unsignedBigInteger('entered_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['student_id', 'exam_subject_id'], 'uk_student_exam_subject');
            $table->index(['school_id', 'exam_id'], 'idx_school_exam');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->enum('type', ['percentage', 'cgpa']);
            $table->tinyInteger('is_default')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('grading_scale_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('grading_scale_id');
            $table->string('grade', 5);
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 3, 1)->nullable();
            $table->string('description', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('grading_scale_id')->references('id')->on('grading_scales')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_scale_rules');
        Schema::dropIfExists('grading_scales');
        Schema::dropIfExists('student_marks');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('exams');
    }
};
