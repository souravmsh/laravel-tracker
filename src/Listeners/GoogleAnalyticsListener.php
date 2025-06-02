<?php

namespace Souravmsh\LaravelTracker\Listeners;

use Illuminate\Support\Facades\Log;
use Souravmsh\LaravelTracker\Events\GoogleAnalyticsEvent;
use Souravmsh\LaravelTracker\Services\GoogleAnalyticsService;

class GoogleAnalyticsListener
{
    public function handle(GoogleAnalyticsEvent $event)
    {
        $ga = new GoogleAnalyticsService();
        $ga->track($event->data);

        // Keep logging the event data for debugging purposes
        if (config("tracker.debug", false)) {
            Log::debug("[LaravelTracker]GoogleAnalyticsListener@handle - tracked successfully",  $event->data);
        }
    }
}
