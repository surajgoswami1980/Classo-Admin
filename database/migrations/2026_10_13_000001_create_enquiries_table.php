<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public lead / enquiry capture. The marketing website (school-erp-web "/")
 * posts the "Request a Demo / Get in touch" form to the API's public
 * POST /enquiry endpoint, which writes a row here. The super-admin reviews
 * and works these leads in the Enquiries module (/admin/enquiries).
 *
 * There is no school_id — enquiries come from prospects who do not yet have
 * a school account on the platform.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->string('organization', 200)->nullable(); // school / institute name
            $table->string('city', 120)->nullable();
            $table->integer('student_count')->nullable();     // rough size → plan fit
            $table->string('subject', 200)->nullable();
            $table->text('message')->nullable();
            // where the lead came from (landing hero, pricing, contact, etc.)
            $table->string('source', 60)->default('website');
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'closed'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable(); // super-admin user id
            $table->timestamp('contacted_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status', 'idx_enquiry_status');
            $table->index('email', 'idx_enquiry_email');
            $table->index('created_at', 'idx_enquiry_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
