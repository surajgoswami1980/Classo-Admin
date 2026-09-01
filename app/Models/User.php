<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Admin\Role as AccessRole;
use App\Models\Admin\Policy;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'school_id',
        'access_role_id',
        'employee_id',
        'designation',
        'department',
        'is_active',
        'is_class_incharge',
        'incharge_class_id',
        'incharge_section_id',
        'last_login_at',
        'last_login_ip',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_class_incharge' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }

    public function directPolicies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class, 'user_direct_permissions');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Scope queries to a specific school (multi-tenant).
     */
    public function scopeForSchool($query, ?int $schoolId = null)
    {
        $schoolId = $schoolId ?? app('current_school_id', null);

        if ($schoolId) {
            return $query->where('school_id', $schoolId);
        }

        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeClassIncharges($query)
    {
        return $query->where('is_class_incharge', true);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Check if user is a super admin (platform level).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is a school admin.
     */
    public function isSchoolAdmin(): bool
    {
        return $this->hasRole('school-admin');
    }

    /**
     * Check if user is a sub-admin with granular permissions.
     */
    public function isSubAdmin(): bool
    {
        return $this->hasRole('sub-admin');
    }

    /**
     * Check if user is a class incharge.
     */
    public function isClassIncharge(): bool
    {
        return $this->is_class_incharge && $this->hasRole('incharge');
    }

    /**
     * Get the school name for display.
     */
    public function getSchoolName(): ?string
    {
        return $this->school?->name;
    }

    /**
     * Record login details.
     */
    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Get available permissions for this user's modules.
     */
    public function getModulePermissions(): array
    {
        if ($this->isSuperAdmin() || $this->isSchoolAdmin()) {
            return ['all'];
        }

        return $this->getAllPermissions()->pluck('name')->toArray();
    }
}
