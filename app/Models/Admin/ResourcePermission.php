<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourcePermission extends Model
{
    protected $table = 'resource_permissions';

    protected $fillable = ['resource_id', 'resource_action_id', 'name', 'url_slug', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ResourceAction::class, 'resource_action_id');
    }
}
