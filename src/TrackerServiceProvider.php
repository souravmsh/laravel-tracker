<?php

namespace Souravmsh\LaravelTracker;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Souravmsh\LaravelTracker\Console\Commands\InstallCommand;
use Souravmsh\LaravelTracker\Http\Middleware\TrackerMiddleware;
use Souravmsh\LaravelTracker\Services\TrackerMiddlewareService;
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
    }

    public function boot(Kernel $kernel)
    {
        // Publish configuration
        $this->publishes(
            [
                __DIR__ . "/../config/tracker.php" => config_path(
                    "tracker.php"
                ),
            ],
            "tracker-config"
        );

        // Publish migrations
        $this->publishes(
            [
                __DIR__ . "/../database/migrations/" => database_path(
                    "migrations"
                ),
            ],
            "tracker-migrations"
        );

        // Publish views
        $this->publishes(
            [
                __DIR__ . "/../resources/views" => resource_path(
                    "views/vendor/tracker"
                ),
            ],
            "tracker-views"
        );

        // Publish assets
        $this->publishes(
            [
                __DIR__ . "/../public/css" => public_path("vendor/tracker"),
            ],
            "tracker-assets"
        );

        // Load routes
        $this->loadRoutesFrom(__DIR__ . "/../routes/api.php");
        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");

        // Load views
        $this->loadViewsFrom(__DIR__ . "/../resources/views", "tracker");

        // Register event listener
        $this->app["events"]->listen(IpApiEvent::class, IpApiListener::class);
        $this->app["events"]->listen(GoogleAnalyticsEvent::class, GoogleAnalyticsListener::class);

        // Register middleware
        $this->app["router"]->aliasMiddleware(
            "tracker",
            TrackerMiddleware::class
        );
        $this->app["router"]->pushMiddlewareToGroup(
            "web",
            TrackerMiddleware::class
        );
        // direct push to middleware
        $kernel->pushMiddleware(TrackerMiddleware::class);

        // Register console command
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }
}
