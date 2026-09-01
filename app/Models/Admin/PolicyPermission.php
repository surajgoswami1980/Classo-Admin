<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyPermission extends Model
{
    protected $table = 'policy_permissions';

    protected $fillable = ['policy_id', 'resource_permission_id'];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function resourcePermission(): BelongsTo
    {
        return $this->belongsTo(ResourcePermission::class);
    }
}
