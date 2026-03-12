<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature   = 'tracker:install {--force : Skip all confirmations}';
    protected $description = 'Install the Laravel Tracker package — publish assets and run migrations';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>🚀 Laravel Tracker — Install</>');
        $this->newLine();

        $force = $this->option('force');

        // ── Publish config ────────────────────────────────────────────────────
        if ($force || $this->confirm('  Publish configuration file?', false)) {
            Artisan::call('vendor:publish', [
                '--tag'      => 'tracker-config',
                '--provider' => 'Souravmsh\\LaravelTracker\\TrackerServiceProvider',
                '--force'    => true,
            ]);
            $this->line('  <fg=green>✓</> Published: <fg=cyan>config/tracker.php</>');
        } else {
            $this->line('  <fg=gray>– Skipped config publish.</>');
        }

        // ── Publish migrations ────────────────────────────────────────────────
        if ($force || $this->confirm('  Publish migration files?', false)) {
            Artisan::call('vendor:publish', [
                '--tag'      => 'tracker-migrations',
                '--provider' => 'Souravmsh\\LaravelTracker\\TrackerServiceProvider',
                '--force'    => true,
            ]);
            $this->line('  <fg=green>✓</> Published: <fg=cyan>database/migrations</>');
        } else {
            $this->line('  <fg=gray>– Skipped migrations publish.</>');
        }

        // ── Publish views ─────────────────────────────────────────────────────
        if ($force || $this->confirm('  Publish view files?', false)) {
            Artisan::call('vendor:publish', [
                '--tag'      => 'tracker-views',
                '--provider' => 'Souravmsh\\LaravelTracker\\TrackerServiceProvider',
                '--force'    => true,
            ]);
            $this->line('  <fg=green>✓</> Published: <fg=cyan>resources/views/vendor/tracker</>');
        } else {
            $this->line('  <fg=gray>– Skipped views publish.</>');
        }

        // ── Publish assets ────────────────────────────────────────────────────
        if ($force || $this->confirm('  Publish asset files?', true)) {
            Artisan::call('vendor:publish', [
                '--tag'      => 'tracker-assets',
                '--provider' => 'Souravmsh\\LaravelTracker\\TrackerServiceProvider',
                '--force'    => true,
            ]);
            $this->line('  <fg=green>✓</> Published: <fg=cyan>public/vendor/tracker</>');
        } else {
            $this->line('  <fg=gray>– Skipped assets publish.</>');
        }

        // ── Run migrations ────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=cyan>Running migrations…</>');
        try {
            Artisan::call('migrate', [
                '--path'  => 'vendor/souravmsh/laravel-tracker/database/migrations',
                '--force' => true,
            ]);
            $output = trim(Artisan::output());
            if ($output) {
                $this->line($output);
            }
            $this->line('  <fg=green>✓</> Migrations completed.');
        } catch (\Exception $e) {
            $this->error('  Migration failed: ' . $e->getMessage());
            $this->line('  <fg=yellow>Ensure your DB connection is configured in .env</>');
            return self::FAILURE;
        }

        // ── Done ──────────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=green;options=bold>✅ Laravel Tracker installed successfully!</>');
        $this->newLine();
        $this->table(['Resource', 'URL'], [
            ['Dashboard', url('tracker/dashboard')],
            ['Visitors',  url('tracker/visitors')],
            ['Referrals', url('tracker/referrals')],
            ['Settings',  url('tracker/settings')],
        ]);
        $this->newLine();
        $this->line('  <fg=gray>Tip: run <fg=cyan>php artisan tracker:help</> to see all available commands.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
