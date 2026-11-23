<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ensure the app-user roles exist on every install.
 *
 * StudentController assigns the 'student' role when a student is added, and
 * the API resolves 'student', 'parent' and 'staff' from model_has_roles. These
 * roles were never seeded, so adding a student failed with
 * "There is no role named `student` for guard `web`".
 *
 * Doing this in a migration (not only the seeder) means a plain
 * `php artisan migrate` fixes existing servers without re-running seeders.
 * Idempotent: existing roles are left untouched.
 */
return new class extends Migration
{
    private array $roles = ['student', 'parent', 'staff'];

    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->roles as $name) {
            $exists = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Spatie caches roles; clear it so the new roles are seen immediately.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally left empty: deleting roles would strip them from every
        // existing student/parent/staff account.
    }
};
