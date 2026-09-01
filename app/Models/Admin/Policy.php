<?php

namespace App\Models\Admin;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    protected $table = 'policies';

    protected $fillable = ['school_id', 'created_by', 'name', 'description', 'status', 'is_deleted'];

    protected $casts = ['status' => 'boolean', 'is_deleted' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function policyPermissions(): HasMany
    {
        return $this->hasMany(PolicyPermission::class);
    }

    public function resourcePermissions(): BelongsToMany
    {
        return $this->belongsToMany(ResourcePermission::class, 'policy_permissions');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)->where('is_deleted', false);
    }
}
