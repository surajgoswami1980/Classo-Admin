<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->string('admission_number', 50)->nullable();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->string('roll_number', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->date('admission_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'transferred', 'graduated'])->default('active');
            $table->string('previous_school', 255)->nullable();
            $table->unsignedBigInteger('transport_route_id')->nullable();
            $table->string('father_name', 255)->nullable();
            $table->string('father_phone', 15)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('mother_phone', 15)->nullable();
            $table->string('guardian_name', 255)->nullable();
            $table->string('guardian_phone', 15)->nullable();
            $table->text('medical_conditions')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'class_id'], 'idx_school_class');
            $table->index(['school_id', 'section_id'], 'idx_school_section');
            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->index(['school_id', 'admission_number'], 'idx_admission_number');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
