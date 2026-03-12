<?php

namespace Souravmsh\LaravelTracker\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Souravmsh\LaravelTracker\Models\TrackerSetting;

class TrackerSettingService
{
    protected const CACHE_KEY = 'tracker_settings_all';
    protected const CACHE_TTL = 3600; // 1 hour in seconds

    /**
     * Load all settings from cache or DB as a key→value array.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            if (!Schema::hasTable('tracker_settings')) {
                return [];
            }
            return TrackerSetting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a single setting value, typed and with a fallback default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (!array_key_exists($key, $settings)) {
            return $default;
        }

        $row = TrackerSetting::where('key', $key)->first();
        $raw = $settings[$key];

        return $this->cast($raw, $row?->type ?? 'string');
    }

    /**
     * Save a single setting and flush the cache.
     */
    public function set(string $key, mixed $value): void
    {
        TrackerSetting::where('key', $key)->update([
            'value'      => $value,
            'updated_at' => now(),
        ]);

        $this->flush();
    }

    /**
     * Bulk-save settings from an associative array and flush cache.
     */
    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            TrackerSetting::where('key', $key)->update([
                'value'      => $value,
                'updated_at' => now(),
            ]);
        }

        $this->flush();
    }

    /**
     * Flush the settings cache.
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Load all settings grouped by their group, with full row data.
     */
    public function grouped(): array
    {
        if (!Schema::hasTable('tracker_settings')) {
            return [];
        }

        return TrackerSetting::all()
            ->groupBy('group')
            ->map(fn($rows) => $rows->keyBy('key'))
            ->toArray();
    }

    /**
     * Merge DB settings into Laravel config so config('tracker.*') calls work.
     */
    public function mergeIntoConfig(): void
    {
        try {
            $settings = $this->all();

            $map = [
                'enabled'          => 'tracker.enabled',
                'debug'            => 'tracker.debug',
                'queue_enabled'    => 'tracker.queue_enabled',
                'log_to_database'  => 'tracker.log_to_database',
                'rate_limit'       => 'tracker.rate_limit',
                'session_lifetime' => 'tracker.session_lifetime',
                'max_input_length' => 'tracker.max_input_length',
                'ip_api_enabled'   => 'tracker.analytics.ip_api.enabled',
                'ip_api_token'     => 'tracker.analytics.ip_api.token',
                'ga_enabled'       => 'tracker.analytics.google.enabled',
                'ga_measurement_id'=> 'tracker.analytics.google.measurement_id',
                'ga_api_secret'    => 'tracker.analytics.google.api_secret',
                'ga_event_name'    => 'tracker.analytics.google.event_name',
                'referral_code_params' => 'tracker.referral_code_params',
                'ignore_paths'         => 'tracker.ignore_paths',
                'allowed_paths'        => 'tracker.allowed_paths',
            ];

            foreach ($map as $dbKey => $configKey) {
                if (array_key_exists($dbKey, $settings)) {
                    $row   = TrackerSetting::where('key', $dbKey)->first();
                    $value = $this->cast($settings[$dbKey], $row?->type ?? 'string');
                    config([$configKey => $value]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail on early boot (e.g., before migrations run)
        }
    }

    protected function cast(mixed $value, string $type): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json'    => json_decode($value, true),
            default   => (string) $value,
        };
    }
}
