<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToSchool Trait
 *
 * Apply this trait to any model that should be automatically scoped
 * by school_id in a multi-tenant context.
 *
 * Usage:
 *   class Student extends Model {
 *       use BelongsToSchool;
 *   }
 *
 * This will automatically add a global scope that filters records
 * by the current school_id from the application container.
 */
trait BelongsToSchool
{
    /**
     * Boot the trait and add global scope.
     */
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school_tenant', function (Builder $builder) {
            $schoolId = app()->bound('current_school_id') ? app('current_school_id') : null;

            if ($schoolId) {
                $builder->where($builder->getModel()->getTable() . '.school_id', $schoolId);
            }
        });

        // Automatically set school_id when creating
        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $schoolId = app()->bound('current_school_id') ? app('current_school_id') : null;
                if ($schoolId) {
                    $model->school_id = $schoolId;
                }
            }
        });
    }

    /**
     * Scope to bypass the tenant filter (for super-admin queries).
     */
    public function scopeWithoutTenantScope(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('school_tenant');
    }

    /**
     * Relationship to the School model.
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School::class);
    }
}
