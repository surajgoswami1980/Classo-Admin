<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceAction extends Model
{
    protected $table = 'resource_actions';

    protected $fillable = ['resource_id', 'action', 'label', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ResourcePermission::class);
    }
}
