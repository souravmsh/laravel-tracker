<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class UninstallCommand extends Command
{
    protected $signature   = 'tracker:uninstall {--force : Skip confirmation prompts}';
    protected $description = 'Uninstall the LaravelTracker package — drops tables and removes published files';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=red;options=bold>⚠  Laravel Tracker — Uninstall</>');
        $this->newLine();

        if (
            !$this->option('force') &&
            !$this->confirm('<fg=red>This will DROP all tracker tables and delete published files. Continue?</>', false)
        ) {
            $this->line('  <fg=yellow>Aborted. No changes were made.</>');
            return self::SUCCESS;
        }

        // ── Drop tables ──────────────────────────────────────────────────────
        $tables = ['tracker_settings', 'tracker_logs', 'tracker_referrals'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("  <fg=green>✓</> Dropped table: <fg=cyan>{$table}</>");
            } else {
                $this->line("  <fg=gray>– Table not found, skipping: {$table}</>");
            }
        }

        // ── Remove published config ──────────────────────────────────────────
        $configPath = config_path('tracker.php');
        if (file_exists($configPath)) {
            unlink($configPath);
            $this->line('  <fg=green>✓</> Removed: <fg=cyan>config/tracker.php</>');
        }

        // ── Remove published views ───────────────────────────────────────────
        $viewsPath = resource_path('views/vendor/tracker');
        if (is_dir($viewsPath)) {
            $this->deleteDirectory($viewsPath);
            $this->line('  <fg=green>✓</> Removed: <fg=cyan>resources/views/vendor/tracker</>');
        }

        // ── Remove published assets ──────────────────────────────────────────
        $assetsPath = public_path('vendor/tracker');
        if (is_dir($assetsPath)) {
            $this->deleteDirectory($assetsPath);
            $this->line('  <fg=green>✓</> Removed: <fg=cyan>public/vendor/tracker</>');
        }

        $this->newLine();
        $this->info('  Laravel Tracker uninstalled successfully.');
        $this->line('  <fg=gray>Remember to remove `TRACKER_*` entries from your .env file.</>');
        $this->newLine();

        return self::SUCCESS;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($dir);
    }
}
