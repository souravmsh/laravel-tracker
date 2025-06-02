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
            TrackerLog::create($this->referralData);
        } catch (\Exception $e) {
            Log::error("[LaravelTracker]TrackerJob@handle() - Failed to log referral in job", [
                "error" => $e->getMessage(),
                "referral_data" => $this->referralData,
            ]);
        }
    }
}
