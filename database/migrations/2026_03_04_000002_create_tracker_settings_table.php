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
            ['key' => 'debug',             'type' => 'boolean', 'group' => 'general',  'label' => 'Debug Mode',             'value' => '0',   'description' => 'Log debug information to laravel.log.'],
            ['key' => 'queue_enabled',     'type' => 'boolean', 'group' => 'general',  'label' => 'Queue Database Writes',  'value' => '1',   'description' => 'Dispatch tracking jobs to the queue instead of writing synchronously.'],
            ['key' => 'log_to_database',   'type' => 'boolean', 'group' => 'general',  'label' => 'Log to Database',        'value' => '1',   'description' => 'Persist tracking records to the database.'],
            ['key' => 'rate_limit',        'type' => 'integer', 'group' => 'general',  'label' => 'Rate Limit (per IP)',    'value' => '300', 'description' => 'Max tracking hits allowed per IP per minute.'],
            ['key' => 'session_lifetime',  'type' => 'integer', 'group' => 'general',  'label' => 'Session Lifetime (min)', 'value' => '1440','description' => 'How long a session window lasts, in minutes.'],
            ['key' => 'max_input_length',  'type' => 'integer', 'group' => 'general',  'label' => 'Max Input Length',       'value' => '255', 'description' => 'Maximum length for sanitized string inputs.'],

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
