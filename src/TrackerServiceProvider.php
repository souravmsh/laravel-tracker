<?php

namespace Souravmsh\LaravelTracker;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Souravmsh\LaravelTracker\Console\Commands\InstallCommand;
use Souravmsh\LaravelTracker\Console\Commands\UninstallCommand;
use Souravmsh\LaravelTracker\Console\Commands\HelpCommand;
use Souravmsh\LaravelTracker\Console\Commands\EnableCommand;
use Souravmsh\LaravelTracker\Console\Commands\DisableCommand;
use Souravmsh\LaravelTracker\Http\Middleware\TrackerMiddleware;
use Souravmsh\LaravelTracker\Services\TrackerMiddlewareService;
use Souravmsh\LaravelTracker\Services\TrackerSettingService;
use Souravmsh\LaravelTracker\Events\IpApiEvent;
use Souravmsh\LaravelTracker\Listeners\IpApiListener;
use Souravmsh\LaravelTracker\Events\GoogleAnalyticsEvent;
use Souravmsh\LaravelTracker\Listeners\GoogleAnalyticsListener;

class TrackerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/tracker.php", "tracker");

        $this->app->singleton(TrackerMiddlewareService::class, function ($app) {
            return new TrackerMiddlewareService();
        });

        $this->app->singleton(TrackerSettingService::class, function ($app) {
            return new TrackerSettingService();
        });
    }

    public function boot(Kernel $kernel)
    {
        // 1. Register console commands (always available so you can enable/uninstall)
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
                HelpCommand::class,
                EnableCommand::class,
                DisableCommand::class,
            ]);
        }

        // 2. Publish assets (always available for installation)
        $this->publishes([__DIR__ . "/../config/tracker.php" => config_path("tracker.php")], "tracker-config");
        $this->publishes([__DIR__ . "/../database/migrations/" => database_path("migrations")], "tracker-migrations");
        $this->publishes([__DIR__ . "/../resources/views" => resource_path("views/vendor/tracker")], "tracker-views");
        $this->publishes([__DIR__ . "/../public" => public_path("vendor/tracker")], "tracker-assets");

        // 3. Merge DB settings into config
        $this->app->make(TrackerSettingService::class)->mergeIntoConfig();

        // 4. Forcefully respect .env if you set TRACKER_ENABLED=false (overrides DB)
        $envTrackerEnabled = env('TRACKER_ENABLED');
        if ($envTrackerEnabled !== null && filter_var($envTrackerEnabled, FILTER_VALIDATE_BOOLEAN) === false) {
            config(['tracker.enabled' => false]);
        }

        // 5. Completely disable all tracker functionalities if tracker is false
        if (!config('tracker.enabled')) {
            return; // Stops here!
        }

        // 6. Load routes, views, events, and middleware ONLY if enabled
        $this->loadRoutesFrom(__DIR__ . "/../routes/api.php");
        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");
        
        $this->loadViewsFrom(__DIR__ . "/../resources/views", "tracker");

        $this->app["events"]->listen(IpApiEvent::class, IpApiListener::class);
        $this->app["events"]->listen(GoogleAnalyticsEvent::class, GoogleAnalyticsListener::class);

        $this->app["router"]->aliasMiddleware("tracker", TrackerMiddleware::class);
        $this->app["router"]->pushMiddlewareToGroup("web", TrackerMiddleware::class);
        $kernel->pushMiddleware(TrackerMiddleware::class);
    }

}
