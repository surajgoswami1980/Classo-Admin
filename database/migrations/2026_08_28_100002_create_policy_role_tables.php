<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('policy_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->foreignId('resource_permission_id')->constrained('resource_permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['policy_id', 'resource_permission_id'], 'policy_permissions_unique');
        });

        // Named "access_roles" to avoid colliding with spatie/laravel-permission's own "roles" table.
        Schema::create('access_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 100);
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('status')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('access_roles')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'policy_id']);
        });

        Schema::create('user_direct_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();

            $table->unique(['user_id', 'policy_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('access_role_id')->nullable()->after('school_id')
                ->constrained('access_roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('access_role_id');
        });

        Schema::dropIfExists('user_direct_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('access_roles');
        Schema::dropIfExists('policy_permissions');
        Schema::dropIfExists('policies');
    }
};
