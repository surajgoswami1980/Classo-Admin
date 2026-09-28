<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events / activities: schools create events (free or paid), students
 * register (paying via Razorpay for paid events), and registrations are
 * tracked with their payment status.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('category', 50)->default('general'); // general, sports, cultural, academic, trip, competition
            $table->string('venue', 255)->nullable();
            $table->string('banner', 500)->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('fee', 10, 2)->default(0);
            $table->integer('capacity')->nullable(); // null = unlimited
            // Audience targeting: 'all' | 'class' | 'section'
            $table->enum('audience_type', ['all', 'class', 'section'])->default('all');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['school_id', 'status'], 'idx_school_status');
            $table->index(['school_id', 'start_at'], 'idx_school_start');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('user_id'); // who registered (student/parent login)
            $table->enum('payment_status', ['not_required', 'pending', 'paid', 'failed'])->default('not_required');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->enum('status', ['registered', 'cancelled', 'attended'])->default('registered');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['event_id', 'user_id'], 'uk_event_user');
            $table->index(['school_id', 'event_id'], 'idx_school_event');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
