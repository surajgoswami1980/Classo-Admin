<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('email')->unique();
            $table->string('phone', 15);
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('pincode', 10);
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('board_affiliation', 100)->nullable();
            $table->integer('established_year')->nullable();
            $table->string('principal_name');
            $table->string('principal_email');
            $table->string('principal_phone', 15);
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])->default('basic');
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->integer('max_students')->default(200);
            $table->integer('max_staff')->default(30);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'subscription_end']);
            $table->index('subscription_plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
