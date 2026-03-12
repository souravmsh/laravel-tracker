<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Souravmsh\LaravelTracker\Models\TrackerSetting;

class DisableCommand extends Command
{
    protected $signature   = 'tracker:disable';
    protected $description = 'Disable tracking globally';

    public function handle(): int
    {
        $this->newLine();

        if (!Schema::hasTable('tracker_settings')) {
            $this->error('  The tracker_settings table does not exist. Please run tracker:install first.');
            return self::FAILURE;
        }

        TrackerSetting::where('key', 'enabled')->update(['value' => '0']);
        Cache::forget('tracker_settings_all');

        $this->line('  <fg=yellow;options=bold>✓ Tracker disabled successfully.</>');
        $this->line('  <fg=gray>Visit tracking is now paused. Run tracker:enable to re-activate.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
