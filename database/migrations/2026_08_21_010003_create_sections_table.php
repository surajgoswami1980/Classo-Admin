<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('class_id');
            $table->string('name', 10);
            $table->integer('capacity')->default(40);
            $table->unsignedBigInteger('class_teacher_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'class_id'], 'idx_school_class');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
