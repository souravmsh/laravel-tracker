<?php

namespace Souravmsh\LaravelTracker\Jobs;

use Souravmsh\LaravelTracker\Models\TrackerLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $referralData;

    public function __construct(array $referralData)
    {
        $this->referralData = $referralData;
    }

    public function handle()
    {
        try {
            $data = $this->referralData;
            $needsGeo = false;

            if (isset($data["_needs_geo"])) {
                $needsGeo = (bool) $data["_needs_geo"];
                unset($data["_needs_geo"]);
            }

            TrackerLog::create($data);

            if ($needsGeo) {
                event(new \Souravmsh\LaravelTracker\Events\IpApiEvent($this->referralData));
            }
        } catch (\Exception $e) {
            Log::error("[LaravelTracker]TrackerJob@handle() - Failed to log referral in job", [
                "error" => $e->getMessage(),
                "referral_data" => $this->referralData,
            ]);
        }
    }
}
