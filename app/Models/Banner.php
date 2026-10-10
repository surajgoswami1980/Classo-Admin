<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'school_id', 'title', 'image', 'thumbnail', 'placement',
        'link_type', 'event_id', 'link_url',
        'audience_type', 'class_id', 'section_id',
        'sort_order', 'is_active', 'start_date', 'end_date', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Currently-live: active + within optional date window.
     */
    public function scopeLive($query)
    {
        $today = now()->toDateString();
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }
}
