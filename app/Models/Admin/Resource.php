<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    protected $table = 'resources';

    protected $fillable = ['name', 'slug', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function actions(): HasMany
    {
        return $this->hasMany(ResourceAction::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ResourcePermission::class);
    }
}
