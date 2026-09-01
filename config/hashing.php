<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    */

    'driver' => 'bcrypt',

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | `verify` is deliberately false: this app shares its `users` table
    | with school-erp-api (NestJS), which hashes passwords with bcryptjs.
    | bcryptjs produces `$2b$`-prefixed hashes, which are cryptographically
    | identical to PHP's own `$2y$` bcrypt for verification purposes, but
    | PHP's password_get_info() only recognizes `$2y$` as a known algorithm.
    | With `verify` true, Laravel's BcryptHasher throws a RuntimeException
    | ("This password does not use the Bcrypt algorithm") on any account
    | created via the API — which includes every student, teacher, and
    | school-admin account, since super-admin/school onboarding happens
    | through the API. `password_verify()` itself works fine across all
    | three prefixes; only this extra metadata check needs disabling.
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => false,
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => false,
    ],

];
