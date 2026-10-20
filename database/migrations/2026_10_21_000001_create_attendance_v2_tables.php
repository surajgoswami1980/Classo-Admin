<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance v2:
 *  - holidays (single day or range, for all / students / staff)
 *  - staff self check-in metadata on staff_attendance (method, location, face score, device)
 *  - staff_devices          : one bound phone per staff member
 *  - staff_face_profiles    : approved reference face photo per staff member
 *  - attendance_attempts    : audit of every self check-in attempt (accepted or rejected)
 *  - biometric_devices / biometric_user_maps / biometric_punches : ZKTeco/eSSL push (ADMS) machines
 *
 * Rules and timings live in schools.settings JSON under "attendance".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('title', 150);
            $table->string('description', 500)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type', 20)->default('holiday');      // holiday | vacation | exam_break | other
            $table->string('applies_to', 10)->default('all');    // all | students | staff
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'start_date', 'end_date']);
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->string('method', 20)->default('manual')->after('status');  // manual | app | geo | face | face_geo | biometric
            $table->unsignedSmallInteger('late_minutes')->nullable()->after('method');
            $table->boolean('early_leave')->default(false)->after('late_minutes');
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->unsignedSmallInteger('check_in_accuracy')->nullable();
            $table->unsignedInteger('check_in_distance')->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->unsignedInteger('check_out_distance')->nullable();
            $table->decimal('face_score', 5, 2)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->string('biometric_device_sn', 60)->nullable();
        });

        Schema::create('staff_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('device_id', 100);
            $table->string('device_name', 150)->nullable();
            $table->string('platform', 20)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index('school_id');
        });

        Schema::create('staff_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->mediumText('image');                          // base64 JPEG, resized on the phone
            $table->string('status', 10)->default('pending');    // pending | approved | rejected
            $table->timestamp('consent_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reject_reason', 255)->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        Schema::create('attendance_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 5);                           // in | out
            $table->string('result', 10);                        // accepted | rejected
            $table->string('reason', 255)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedSmallInteger('accuracy')->nullable();
            $table->unsignedInteger('distance')->nullable();
            $table->decimal('face_score', 5, 2)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['school_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('serial_number', 60)->unique();
            $table->string('name', 100);
            $table->string('location', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('attlog_stamp', 30)->nullable();
            $table->timestamps();
            $table->index('school_id');
        });

        Schema::create('biometric_user_maps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('device_pin', 30);                    // user ID enrolled on the machine
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['school_id', 'device_pin']);
            $table->unique(['school_id', 'user_id']);
        });

        Schema::create('biometric_punches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('device_id');
            $table->string('serial_number', 60);
            $table->string('device_pin', 30);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('punched_at');                      // school-local wall time as sent by the machine
            $table->string('punch_state', 5)->nullable();
            $table->string('verify_mode', 5)->nullable();
            $table->string('result', 20)->default('pending');    // applied | unmapped | holiday | disabled | ignored
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['serial_number', 'device_pin', 'punched_at'], 'uk_punch');
            $table->index(['school_id', 'punched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_punches');
        Schema::dropIfExists('biometric_user_maps');
        Schema::dropIfExists('biometric_devices');
        Schema::dropIfExists('attendance_attempts');
        Schema::dropIfExists('staff_face_profiles');
        Schema::dropIfExists('staff_devices');
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dropColumn([
                'method', 'late_minutes', 'early_leave', 'check_in_lat', 'check_in_lng', 'check_in_accuracy',
                'check_in_distance', 'check_out_lat', 'check_out_lng', 'check_out_distance', 'face_score',
                'device_id', 'biometric_device_sn',
            ]);
        });
        Schema::dropIfExists('holidays');
    }
};
