<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day']);
            $table->unsignedBigInteger('marked_by');
            $table->string('remarks', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['student_id', 'date'], 'uk_student_date');
            $table->index(['school_id', 'class_id', 'date'], 'idx_school_class_date');
            $table->index(['school_id', 'student_id', 'date'], 'idx_student_date_range');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
