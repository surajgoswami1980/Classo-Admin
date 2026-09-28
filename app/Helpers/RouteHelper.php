<?php

if (!function_exists('panel_route')) {
    /**
     * Returns the correct route based on user role.
     * Super admin → admin.* prefix, School users → user.* prefix
     */
    function panel_route(string $name, $parameters = [], bool $absolute = true): string
    {
        $user = auth()->user();
        $prefix = ($user && $user->hasRole('super-admin')) ? 'admin' : 'user';

        $fullRoute = $prefix . '.' . $name;

        // Check if route exists
        if (\Route::has($fullRoute)) {
            return route($fullRoute, $parameters, $absolute);
        }

        // Fallback to admin route (for super-admin-only routes like schools)
        if (\Route::has('admin.' . $name)) {
            return route('admin.' . $name, $parameters, $absolute);
        }

        return '#';
    }
}

if (!function_exists('panel_prefix')) {
    /**
     * The URL prefix for the current user's panel: 'admin' for super-admin,
     * otherwise 'user'. Useful for building dynamic (JS) form actions.
     */
    function panel_prefix(): string
    {
        $user = auth()->user();

        return ($user && $user->hasRole('super-admin')) ? 'admin' : 'user';
    }
}
