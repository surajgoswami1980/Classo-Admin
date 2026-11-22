<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original schools migration declared contact and principal columns as
 * NOT NULL with no default, but the onboarding form never collects the
 * principal fields and treats contact details as optional. Strict MySQL then
 * rejects every onboarding insert ("Field 'principal_name' doesn't have a
 * default value").
 *
 * Make these columns nullable, matching the NestJS SchoolEntity, which already
 * treats them as nullable. Types, lengths and the unique index on email are
 * kept unchanged. Existing data is untouched.
 */
return new class extends Migration
{
    /** column => SQL type (without NULL / NOT NULL). */
    private array $columns = [
        'email' => 'VARCHAR(255)',
        'phone' => 'VARCHAR(15)',
        'address' => 'TEXT',
        'city' => 'VARCHAR(100)',
        'state' => 'VARCHAR(100)',
        'pincode' => 'VARCHAR(10)',
        'principal_name' => 'VARCHAR(255)',
        'principal_email' => 'VARCHAR(255)',
        'principal_phone' => 'VARCHAR(15)',
    ];

    public function up(): void
    {
        foreach ($this->columns as $column => $type) {
            if (Schema::hasColumn('schools', $column)) {
                DB::statement("ALTER TABLE schools MODIFY `{$column}` {$type} NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $column => $type) {
            if (!Schema::hasColumn('schools', $column)) {
                continue;
            }
            if ($column === 'email') {
                // Unique column: fill blanks with a unique placeholder, not ''.
                DB::statement("UPDATE schools SET email = CONCAT('school-', id, '@placeholder.invalid') WHERE email IS NULL OR email = ''");
            } else {
                DB::statement("UPDATE schools SET `{$column}` = '' WHERE `{$column}` IS NULL");
            }
            DB::statement("ALTER TABLE schools MODIFY `{$column}` {$type} NOT NULL");
        }
    }
};
