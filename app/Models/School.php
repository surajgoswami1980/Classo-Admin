<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'logo',
        'website',
        'board_affiliation',
        'established_year',
        'principal_name',
        'principal_email',
        'principal_phone',
        'subscription_plan',
        'subscription_price',
        'subscription_start',
        'subscription_end',
        'max_students',
        'max_staff',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'max_students' => 'integer',
        'max_staff' => 'integer',
        'subscription_price' => 'decimal:2',
    ];

    /**
     * Price this school actually pays: the agreed custom price if one was
     * set, otherwise the plan's list price.
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->subscription_price !== null) {
            return (float) $this->subscription_price;
        }

        $plans = \App\Http\Controllers\SuperAdmin\SubscriptionController::PLANS;

        return (float) ($plans[$this->subscription_plan]['price'] ?? 0);
    }

    // ─── Relationships ───────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('subscription_end', '<=', now()->addDays($days))
                     ->where('subscription_end', '>=', now());
    }
}
