<?php

namespace App\Support;

/**
 * Shared slug-canonicalization logic used by both CheckResourcePermission
 * (to check access) and ResourcePermissionSeeder (to build the permission
 * catalog), so the two never drift apart.
 */
class ResourceSlug
{
    /**
     * Route-name verb -> permission action.
     */
    protected static array $verbMap = [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
    ];

    /**
     * Route names (with "user."/"admin." prefix already stripped) that are
     * always reachable and never gated behind a resource permission.
     */
    public static array $except = [
        'dashboard',
        'profile',
        'profile.update',
        'profile.password',
        'settings',
        'settings.update',
    ];

    public static function canonicalize(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $last = array_pop($parts);
        $parts[] = static::$verbMap[$last] ?? $last;

        return implode('.', $parts);
    }

    /**
     * The resource key a canonical slug belongs to: its first dot-segment.
     */
    public static function resourceKey(string $canonicalSlug): string
    {
        return explode('.', $canonicalSlug)[0];
    }

    /**
     * The action a canonical slug represents: its last dot-segment.
     */
    public static function actionKey(string $canonicalSlug): string
    {
        $parts = explode('.', $canonicalSlug);

        return end($parts);
    }
}
