<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `users` and `schools` were created via Laravel's `$table->timestamps()`
 * helper, which (unlike every other table in this schema, all hand-written
 * with `DEFAULT CURRENT_TIMESTAMP`) leaves created_at/updated_at with no
 * DB-level default. Eloquent papers over this by always setting both
 * columns explicitly in PHP before every insert/update — but
 * school-erp-api (NestJS/TypeORM) relies on the database default for
 * @CreateDateColumn() on INSERT (it emits `DEFAULT` in the SQL rather than
 * computing the timestamp itself), so every account or school created via
 * the API got a permanently NULL created_at. Raw SQL (not schema builder
 * ->change(), which requires re-declaring the full nullable state
 * correctly for a timestamp column) is the reliable way to add these
 * defaults without accidentally flipping the columns to NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE users MODIFY updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE schools MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE schools MODIFY updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement("UPDATE users SET created_at = COALESCE(created_at, updated_at, NOW()) WHERE created_at IS NULL");
        DB::statement("UPDATE schools SET created_at = COALESCE(created_at, updated_at, NOW()) WHERE created_at IS NULL");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY created_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE users MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE schools MODIFY created_at TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE schools MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');
    }
};
