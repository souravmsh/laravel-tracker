<?php

namespace Souravmsh\LaravelTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "tracker:install";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Install the LaravelTracker package by publishing assets and running migrations";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Installing LaravelTracker package...");

        // Publish configuration
        if (
            $this->confirm(
                "Do you want to publish the configuration files?",
                false
            )
        ) {
            $this->info("Publishing configuration...");
            Artisan::call("vendor:publish", [
                "--tag" => "tracker-config",
                "--provider" =>
                    "Souravmsh\LaravelTracker\TrackerServiceProvider",
                "--force" => true,
            ]);
            $this->comment(Artisan::output());
        } else {
            $this->comment("Skipped publishing configuration.");
        }

        // Publish migrations
        if (
            $this->confirm(
                "Do you want to publish the migration files?",
                default: false
            )
        ) {
            $this->info("Publishing migrations...");
            Artisan::call("vendor:publish", [
                "--tag" => "tracker-migrations",
                "--provider" =>
                    "Souravmsh\LaravelTracker\TrackerServiceProvider",
                "--force" => true,
            ]);
            $this->comment(Artisan::output());
        } else {
            $this->comment("Skipped publishing migrations.");
        }

        // Publish views
        if ($this->confirm("Do you want to publish the view files?", false)) {
            $this->info("Publishing views...");
            Artisan::call("vendor:publish", [
                "--tag" => "tracker-views",
                "--provider" =>
                    "Souravmsh\LaravelTracker\TrackerServiceProvider",
                "--force" => true,
            ]);
            $this->comment(Artisan::output());
        } else {
            $this->comment("Skipped publishing views.");
        }

        // Publish assets (commented out in original code, keeping it commented)
        if ($this->confirm("Do you want to publish the asset files?", true)) {
            $this->info("Publishing assets...");
            Artisan::call("vendor:publish", [
                "--tag" => "tracker-assets",
                "--provider" =>
                    "Souravmsh\LaravelTracker\TrackerServiceProvider",
                "--force" => true,
            ]);
            $this->comment(Artisan::output());
        } else {
            $this->comment("Skipped publishing assets.");
        }

        // Run migrations without asking
        $this->info("Running migrations...");
        try {
            Artisan::call("migrate", [
                "--path" =>
                    "vendor/souravmsh/laravel-tracker/database/migrations",
            ]);
            $this->comment(Artisan::output());
        } catch (\Exception $e) {
            $this->error("Migration failed: " . $e->getMessage());
            $this->warn(
                "Please ensure your database connection is configured correctly in .env"
            );
            return 1;
        }

        // Provide post-installation instructions
        $this->info("LaravelTracker package installed successfully!");
        $this->comment("Next steps:");
        $this->comment(
            "- Add environment variables to .env (see config/referral.php for options)."
        );
        $this->comment("- Access the dashboard at /tracker/dashboard.");
        $this->comment(
            "- Use the API at /api/tracker/referrals or /api/tracker/visitors."
        );
        $this->comment("For more details, see the README.md file.");

        return 0;
    }
}
