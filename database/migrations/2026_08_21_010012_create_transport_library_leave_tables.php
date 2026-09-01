<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transport
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('vehicle_number', 20);
            $table->integer('capacity');
            $table->enum('vehicle_type', ['bus', 'van', 'auto'])->default('bus');
            $table->date('insurance_expiry')->nullable();
            $table->date('fitness_expiry')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 15)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });

        Schema::create('transport_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('route_id');
            $table->string('name', 100);
            $table->integer('sequence_order');
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'route_id'], 'idx_route');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
        });

        Schema::create('student_transport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('stop_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['student_id', 'academic_session_id'], 'uk_student_session');
            $table->index(['school_id', 'route_id'], 'idx_school_route');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('transport_stops')->onDelete('cascade');
        });

        // Library
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('rack_location', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('school_id');
            $table->index(['school_id', 'isbn'], 'idx_isbn');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('library_book_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('issued_to_user_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->tinyInteger('fine_paid')->default(0);
            $table->enum('status', ['issued', 'returned', 'overdue'])->default('issued');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->index(['school_id', 'issued_to_user_id'], 'idx_school_user');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('library_books')->onDelete('cascade');
            $table->foreign('issued_to_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('leave_type', ['casual', 'sick', 'earned', 'maternity', 'other']);
            $table->date('from_date');
            $table->date('to_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'user_id'], 'idx_school_user');
            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Audit Log
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'user_id'], 'idx_school_user');
            $table->index(['entity_type', 'entity_id'], 'idx_entity');
            $table->index('created_at', 'idx_created');
        });

        // Subscription Plans (platform-level)
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('tier', ['starter', 'growth', 'enterprise']);
            $table->integer('max_students');
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('annual_price', 10, 2);
            $table->json('features')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Parent-Student Link
        Schema::create('parent_student_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('parent_user_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('relationship', ['father', 'mother', 'guardian']);
            $table->tinyInteger('is_primary')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['parent_user_id', 'student_id'], 'uk_parent_student');
            $table->index(['school_id', 'parent_user_id'], 'idx_school_parent');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('parent_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });

        // Teacher Assignments (subject/class mapping)
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['teacher_id', 'class_id', 'section_id', 'subject_id', 'academic_session_id'], 'uk_assignment');
            $table->index(['school_id', 'teacher_id'], 'idx_school_teacher');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('parent_student_links');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('library_book_issues');
        Schema::dropIfExists('library_books');
        Schema::dropIfExists('student_transport');
        Schema::dropIfExists('transport_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('vehicles');
    }
};
