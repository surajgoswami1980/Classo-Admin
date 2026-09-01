<?php

namespace App\Models\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A school-scoped, permission-bearing role for the admin panel's team
 * management feature. Named "access_roles" in the DB to avoid clashing
 * with spatie/laravel-permission's own "roles" table.
 */
class Role extends Model
{
    protected $table = 'access_roles';

    protected $fillable = ['school_id', 'name', 'created_by', 'status', 'is_deleted'];

    protected $casts = ['status' => 'boolean', 'is_deleted' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class, 'role_permissions', 'role_id', 'policy_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'access_role_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)->where('is_deleted', false);
    }
}
