<?php

namespace Souravmsh\LaravelTracker\Listeners;

use Illuminate\Support\Facades\Log;
use Souravmsh\LaravelTracker\Events\IpApiEvent;
use Souravmsh\LaravelTracker\Services\IpApiService;

class IpApiListener
{
    public function handle(IpApiEvent $event)
    {
        $ipa = new IpApiService;
        $ipa->track($event->data);

        // Keep logging the event data for debugging purposes
        if (config("tracker.debug", false)) {
            Log::debug("[LaravelTracker]IpApiListener@handle - IP API data tracked successfully",  $event->data);
        }
    }
}
