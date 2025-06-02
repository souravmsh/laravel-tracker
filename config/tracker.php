<?php

/**
 * Referral tracking configuration
 * sample url: http://0.0.0.0:8804/home?_rf=10001&utm_source=google&utm_medium=cpc&utm_campaign=spring_sale&
 * sample url: http://0.0.0.0:8804/home?_rf=10002&utm_source=google&utm_medium=cpc&utm_campaign=spring_sale&
 */

return [
    "enabled" => env("TRACKER_ENABLED", true),
    "debug" => env("TRACKER_DEBUG_ENABLED", true),
    "queue_enabled" => env("TRACKER_QUEUE_ENABLED", true),
    "log_to_database" => env("TRACKER_LOG_TO_DATABASE", true),
    "rate_limit" => env("TRACKER_RATE_LIMIT", 300),
    "session_lifetime" => env("TRACKER_SESSION_LIFETIME", 1440), // In minutes
    "max_input_length" => env("TRACKER_MAX_INPUT_LENGTH", 255),
    "analytics" => [
        "google" => [
            "enabled"        => env("TRACKER_GOOGLE_ANALYTICS_ENABLED", true),
            "measurement_id" => env("TRACKER_GOOGLE_ANALYTICS_MEASUREMENT_ID", null),
            "api_secret"     => env("TRACKER_GOOGLE_ANALYTICS_API_SECRET", null),
            "client_id"      => env("TRACKER_GOOGLE_ANALYTICS_CLIENT_ID", null),
            "event_name"     => env("TRACKER_GOOGLE_ANALYTICS_EVENT_NAME", null),
        ],
        "ip_api" => [
            "enabled" => env("TRACKER_IPAPI_ENABLED", true),
            "token" => env("TRACKER_IPAPI_TOKEN", null),
        ],
    ],
    "referral_code_params" => ["ref", "code", "track_code", "_rf"],
    'ignore_paths' => [
        "tracker/*",
        "_debugbar/*",
        "cms/ads-manager/ads-placements",
        "api/*",
        "*.xml",
        "*.json",
        "*.map",
        "*.css",
        "*.js",
        "*.png",
        "*.jpg",
        "*.jpeg",
        "*.gif",
        "*.ico",
        "*.svg"
    ],
];
