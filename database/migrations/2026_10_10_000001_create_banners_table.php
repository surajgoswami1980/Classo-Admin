<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School banners shown in the student/parent/teacher web + mobile app.
 *
 * Two placements:
 *   - "slider": multiple active banners that scroll at the top of the home
 *     screen (carousel). A school can have many active sliders.
 *   - "popup": a single active banner shown as a modal on login/home. A
 *     school may have at most ONE active popup at a time (enforced in the
 *     controller when activating).
 *
 * A banner may optionally link to an event (link_type='event') so tapping it
 * opens that event; or to an external/internal URL (link_type='url'); or
 * nothing (link_type='none').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title', 200)->nullable();
            $table->string('image', 500);            // stored path (public disk) or URL
            $table->string('thumbnail', 500)->nullable();
            $table->enum('placement', ['slider', 'popup'])->default('slider');
            $table->enum('link_type', ['none', 'event', 'url'])->default('none');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->enum('audience_type', ['all', 'class', 'section'])->default('all');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'placement', 'is_active'], 'idx_banner_school_placement');
            $table->index(['school_id', 'sort_order'], 'idx_banner_sort');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
