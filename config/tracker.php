<?php

/**
 * Laravel Tracker — Base configuration
 *
 * All runtime settings (enabled, debug, rate_limit, Google Analytics, IP API, etc.)
 * are managed via the database and editable at /tracker/settings.
 *
 * This file contains only structural settings that cannot be changed at runtime.
 */

return [
    'enabled'         => env('TRACKER_ENABLED', true),
    'title'           => env('APP_NAME', 'TRACKER').' // ANALYTICS',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    | Configure the URL prefix and middleware for the tracker dashboard.
    */
    'routes' => [
        'prefix'     => 'tracker',
        'middleware' => ['web', 'tracker'], // You can add 'auth', 'role:admin' etc.
    ],

    /*
    |--------------------------------------------------------------------------
    | Referral Code Query Parameters
    |--------------------------------------------------------------------------
    | URL query parameters that will be inspected for referral codes.
    */
    'referral_code_params' => ['ref', 'code', 'track_code', '_rf'],

    /*
    |--------------------------------------------------------------------------
    | Ignored Paths
    |--------------------------------------------------------------------------
    | Requests matching these patterns will not be tracked.
    */
    'ignore_paths' => [
        'tracker/*',
        '_debugbar/*',
        'cms/ads-manager/ads-placements',
        'api/*',
        '*.xml',
        '*.json',
        '*.map',
        '*.css',
        '*.js',
        '*.png',
        '*.jpg',
        '*.jpeg',
        '*.gif',
        '*.ico',
        '*.svg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Paths
    |--------------------------------------------------------------------------
    | If not empty, only requests matching these patterns will be tracked.
    */
    'allowed_paths' => [],

    /*
    |--------------------------------------------------------------------------
    | Fallback defaults (used before DB settings are loaded or for fresh installs)
    |--------------------------------------------------------------------------
    */
    'debug'           => false,
    'queue_enabled'   => false,
    'log_to_database' => true,
    'rate_limit'      => 300,
    'session_lifetime'=> 1440,
    'max_input_length'=> 255,
    'layout'          => 'tracker::app',
    'cache_ttl'       => env('TRACKER_CACHE_TTL', 300), // TTL in seconds (0 to disable)
    'analytics' => [
        'google' => [
            'enabled'        => false,
            'measurement_id' => null,
            'api_secret'     => null,
            'event_name'     => 'visitor_tracking',
        ],
        'ip_api' => [
            'enabled' => true,
            'token'   => null,
        ],
    ],

];
