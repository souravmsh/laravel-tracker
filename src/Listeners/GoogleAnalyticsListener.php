<?php

namespace Souravmsh\LaravelTracker\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Souravmsh\LaravelTracker\Events\GoogleAnalyticsEvent;
use Souravmsh\LaravelTracker\Services\GoogleAnalyticsService;
use Exception;

class GoogleAnalyticsListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(GoogleAnalyticsEvent $event)
    {
        try {
            if (!config("tracker.analytics.google.enabled", false)) {
                return;
            }

            $ga = new GoogleAnalyticsService();
            $ga->track($event->data);

            if (config("tracker.debug", false)) {
                Log::debug("[LaravelTracker] GoogleAnalyticsListener@handle - Event processed", $event->data);
            }
        } catch (Exception $e) {
            Log::error("[LaravelTracker] GoogleAnalyticsListener@handle - Execution failed: " . $e->getMessage());
        }
    }
}

