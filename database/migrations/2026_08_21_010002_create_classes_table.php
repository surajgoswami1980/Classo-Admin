<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->string('name', 50);
            $table->integer('numeric_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'academic_session_id'], 'idx_school_session');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
