<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Souravmsh\LaravelTracker\Models\TrackerSetting;

class EnableCommand extends Command
{
    protected $signature   = 'tracker:enable';
    protected $description = 'Enable tracking globally';

    public function handle(): int
    {
        $this->newLine();

        if (!Schema::hasTable('tracker_settings')) {
            $this->error('  The tracker_settings table does not exist. Please run tracker:install first.');
            return self::FAILURE;
        }

        TrackerSetting::where('key', 'enabled')->update(['value' => '1']);
        Cache::forget('tracker_settings_all');

        $this->line('  <fg=green;options=bold>✓ Tracker enabled successfully.</>');
        $this->line('  <fg=gray>Visit tracking is now active for all requests.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
