<?php

use App\Models\Admin\Policy;
use App\Support\ResourceSlug;
use Illuminate\Support\Facades\Cache;

if (!function_exists('current_school_id')) {
    /**
     * The school currently in context: the impersonated school (super-admin
     * "viewing as" a school) if set, otherwise the logged-in user's own school.
     */
    function current_school_id(): ?int
    {
        return session('impersonating_school_id') ?? auth()->user()?->school_id;
    }
}

if (!function_exists('is_impersonating')) {
    function is_impersonating(): bool
    {
        return session()->has('impersonating_school_id');
    }
}

if (!function_exists('resource_permission_slugs')) {
    /**
     * The set of resource-permission url_slugs the current user may act on.
     * Returns ['*'] as a bypass sentinel for super-admin/school-admin, who
     * always have full access within their scope.
     */
    function resource_permission_slugs(): array
    {
        static $memo = [];

        $user = auth()->user();
        if (!$user) {
            return [];
        }

        if ($user->hasRole('super-admin') || $user->hasRole('school-admin')) {
            return ['*'];
        }

        $cacheKey = "resource_perms:user:{$user->id}";
        if (array_key_exists($cacheKey, $memo)) {
            return $memo[$cacheKey];
        }

        return $memo[$cacheKey] = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            if ($user->access_role_id) {
                $role = $user->accessRole;
                if (!$role || !$role->status || $role->is_deleted) {
                    return [];
                }
                $policyIds = $role->policies()->active()->pluck('policies.id');
            } else {
                $policyIds = $user->directPolicies()->active()->pluck('policies.id');
            }

            if ($policyIds->isEmpty()) {
                return [];
            }

            return Policy::whereIn('id', $policyIds)
                ->active()
                ->with('resourcePermissions:id,url_slug')
                ->get()
                ->pluck('resourcePermissions.*.url_slug')
                ->flatten()
                ->unique()
                ->values()
                ->all();
        });
    }
}

if (!function_exists('has_resource_permission')) {
    function has_resource_permission(string $slug): bool
    {
        $slugs = resource_permission_slugs();

        return in_array('*', $slugs, true) || in_array($slug, $slugs, true);
    }
}

if (!function_exists('forget_resource_permission_cache')) {
    function forget_resource_permission_cache(int $userId): void
    {
        Cache::forget("resource_perms:user:{$userId}");
    }
}

if (!function_exists('canonical_resource_slug')) {
    /**
     * Maps a route name (with any "user."/"admin." prefix already stripped)
     * to its canonical resource-permission slug, e.g. "students.index" -> "students.view".
     */
    function canonical_resource_slug(string $routeName): string
    {
        return ResourceSlug::canonicalize($routeName);
    }
}
