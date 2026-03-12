<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tracker_settings')) {
            return;
        }

        $now = now();
        $extraSettings = [
            [
                'key' => 'referral_code_params',
                'type' => 'json',
                'group' => 'general',
                'label' => 'Referral Code Parameters',
                'value' => json_encode(['ref', 'code', 'track_code', '_rf']),
                'description' => 'URL query parameters to inspect for referral codes (JSON array).',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'ignore_paths',
                'type' => 'json',
                'group' => 'general',
                'label' => 'Ignored Paths',
                'value' => json_encode([
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
                ]),
                'description' => 'Paths matching these patterns will not be tracked (JSON array).',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'allowed_paths',
                'type' => 'json',
                'group' => 'general',
                'label' => 'Allowed Paths',
                'value' => json_encode([]),
                'description' => 'If not empty, only requests matching these patterns will be tracked (JSON array).',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($extraSettings as $setting) {
            DB::table('tracker_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        DB::table('tracker_settings')
            ->whereIn('key', ['referral_code_params', 'ignore_paths', 'allowed_paths'])
            ->delete();
    }
};
