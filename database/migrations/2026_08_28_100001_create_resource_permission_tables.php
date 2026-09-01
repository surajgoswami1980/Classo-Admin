<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('resource_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->string('action', 50);
            $table->string('label', 100);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['resource_id', 'action']);
        });

        Schema::create('resource_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
            $table->foreignId('resource_action_id')->constrained('resource_actions')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('url_slug', 150)->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_permissions');
        Schema::dropIfExists('resource_actions');
        Schema::dropIfExists('resources');
    }
};
