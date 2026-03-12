<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    protected $signature   = 'tracker:help';
    protected $description = 'Show all available Laravel Tracker commands and their descriptions';

    public function handle(): int
    {
        $this->newLine();
        $this->line("  <fg=cyan;options=bold>███████████████████████████████████████</>");
        $this->line("  <fg=cyan;options=bold>        Laravel Tracker — Help           </>");
        $this->line("  <fg=cyan;options=bold>███████████████████████████████████████</>");
        $this->newLine();

        $commands = [
            ['tracker:help',      'Show this help message'],
            ['tracker:install',   'Publish assets, run migrations, and set up the package'],
            ['tracker:uninstall', 'Remove tracker tables and published files'],
            ['tracker:enable',    'Enable tracking globally (sets tracker.enabled = true)'],
            ['tracker:disable',   'Disable tracking globally (sets tracker.enabled = false)'],
        ];

        $this->table(
            ['<info>Command</info>', '<info>Description</info>'],
            $commands
        );

        $this->newLine();
        $this->line("  <fg=yellow>Dashboard URL:</> <href=" . url('tracker/dashboard') . ">" . url('tracker/dashboard') . "</href>");
        $this->line("  <fg=yellow>Settings URL:</> <href=" . url('tracker/settings') . ">" . url('tracker/settings') . "</href>");
        $this->newLine();
        $this->line('  <fg=gray>Documentation: https://github.com/souravmsh/laravel-tracker</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
