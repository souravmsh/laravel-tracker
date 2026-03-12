<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracker_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, boolean, integer, json
            $table->string('group', 50)->default('general');
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $defaults = [
            // General
            ['key' => 'enabled',           'type' => 'boolean', 'group' => 'general',  'label' => 'Enable Tracker',         'value' => '1',   'description' => 'Enable or disable visit tracking globally.'],
            ['key' => 'title',             'type' => 'string' , 'group' => 'general',  'label' => 'Analytics Title',        'value' => 'Tracker // Analytics', 'description' => 'The title of the analytics page.'],
            ['key' => 'debug',             'type' => 'boolean', 'group' => 'general',  'label' => 'Debug Mode',             'value' => '0',   'description' => 'Log debug information to laravel.log.'],
            ['key' => 'queue_enabled',     'type' => 'boolean', 'group' => 'general',  'label' => 'Queue Database Writes',  'value' => '1',   'description' => 'Dispatch tracking jobs to the queue instead of writing synchronously.'],
            ['key' => 'log_to_database',   'type' => 'boolean', 'group' => 'general',  'label' => 'Log to Database',        'value' => '1',   'description' => 'Persist tracking records to the database.'],
            ['key' => 'rate_limit',        'type' => 'integer', 'group' => 'general',  'label' => 'Rate Limit (per IP)',    'value' => '300', 'description' => 'Max tracking hits allowed per IP per minute.'],
            ['key' => 'session_lifetime',  'type' => 'integer', 'group' => 'general',  'label' => 'Session Lifetime (min)', 'value' => '1440','description' => 'How long a session window lasts, in minutes.'],
            ['key' => 'max_input_length',  'type' => 'integer', 'group' => 'general',  'label' => 'Max Input Length',       'value' => '255', 'description' => 'Maximum length for sanitized string inputs.'],
            ['key' => 'layout',            'type' => 'string' , 'group' => 'general',  'label' => 'Master Layout',          'value' => 'tracker::app', 'description' => 'The Blade layout to extend (e.g., tracker::app or layouts.app).'],
            ['key' => 'cache_ttl',        'type' => 'integer', 'group' => 'general',  'label' => 'Dashboard Cache TTL',    'value' => '300', 'description' => 'TTL in seconds for dashboard reports (0 to disable).'],
            ['key' => 'route_prefix',     'type' => 'string' , 'group' => 'general',  'label' => 'Route Prefix',           'value' => 'tracker', 'description' => 'The URL prefix for the tracker dashboard.'],
            ['key' => 'route_middleware', 'type' => 'json'   , 'group' => 'general',  'label' => 'Route Middleware',       'value' => json_encode(['web', 'tracker']), 'description' => 'Middleware applied to tracker routes (JSON array).'],

            // Advanced Filters
            ['key' => 'referral_code_params', 'type' => 'json', 'group' => 'general',  'label' => 'Referral Code Parameters', 'value' => json_encode(['ref', 'code', 'track_code', '_rf']), 'description' => 'URL query parameters to inspect for referral codes (JSON array).'],
            ['key' => 'ignore_paths',         'type' => 'json', 'group' => 'general',  'label' => 'Ignored Paths',           'value' => json_encode(['tracker/*', '_debugbar/*', 'cms/ads-manager/ads-placements', 'api/*', '*.xml', '*.json', '*.map', '*.css', '*.js', '*.png', '*.jpg', '*.jpeg', '*.gif', '*.ico', '*.svg']), 'description' => 'Paths matching these patterns will not be tracked (JSON array).'],
            ['key' => 'allowed_paths',        'type' => 'json', 'group' => 'general',  'label' => 'Allowed Paths',           'value' => json_encode([]), 'description' => 'If not empty, only requests matching these patterns will be tracked (JSON array).'],

            // IP API
            ['key' => 'ip_api_enabled',   'type' => 'boolean', 'group' => 'ip_api',   'label' => 'Enable IP Geolocation', 'value' => '1',   'description' => 'Fetch country/city data via ipapi.co.'],
            ['key' => 'ip_api_token',     'type' => 'string',  'group' => 'ip_api',   'label' => 'IP API Token',           'value' => null,  'description' => 'Optional paid token for ipapi.co to increase rate limits.'],

            // Google Analytics
            ['key' => 'ga_enabled',       'type' => 'boolean', 'group' => 'google',   'label' => 'Enable Google Analytics', 'value' => '0',  'description' => 'Send events to GA4 via Measurement Protocol.'],
            ['key' => 'ga_measurement_id','type' => 'string',  'group' => 'google',   'label' => 'Measurement ID',          'value' => null, 'description' => 'Your GA4 property ID, e.g. G-XXXXXXXXXX.'],
            ['key' => 'ga_api_secret',    'type' => 'string',  'group' => 'google',   'label' => 'API Secret',              'value' => null, 'description' => 'API secret created in GA4 > Admin > Data Streams.'],
            ['key' => 'ga_event_name',    'type' => 'string',  'group' => 'google',   'label' => 'Event Name',              'value' => 'visitor_tracking', 'description' => 'GA4 event name for tracker hits.'],
        ];

        $now = now();
        foreach ($defaults as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('tracker_settings')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('tracker_settings');
    }
};
