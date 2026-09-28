<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hostel management: buildings/blocks -> rooms (with capacity) -> student
 * allocations. Fees for hostel can be collected through the existing
 * payment pipeline if desired later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->enum('type', ['boys', 'girls', 'mixed'])->default('boys');
            $table->string('warden_name', 100)->nullable();
            $table->string('warden_phone', 15)->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('block_id');
            $table->string('room_number', 30);
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory'])->default('double');
            $table->integer('capacity')->default(2);
            $table->decimal('fee_per_month', 10, 2)->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'block_id'], 'idx_school_block');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('block_id')->references('id')->on('hostel_blocks')->onDelete('cascade');
        });

        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('student_id');
            $table->date('allocated_from');
            $table->date('vacated_on')->nullable();
            $table->enum('status', ['active', 'vacated'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'room_id'], 'idx_school_room');
            $table->index(['school_id', 'student_id'], 'idx_school_student');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('hostel_rooms')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostel_blocks');
    }
};
