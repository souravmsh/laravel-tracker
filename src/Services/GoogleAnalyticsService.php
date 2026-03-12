<?php

namespace Souravmsh\LaravelTracker\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticsService
{
    protected $analytics;
    protected $measurementId;
    protected $apiSecret;

    public function __construct()
    {
        $this->measurementId = config('tracker.analytics.google.measurement_id');
        $this->apiSecret     = config('tracker.analytics.google.api_secret');
    }

    /**
     * Generate a GA4-style client_id (e.g., 12345.67890)
     */
    private function generateClientId(): string
    {
        $random1 = mt_rand(10000, 999999999);
        $random2 = mt_rand(10000, 999999999);
        return "{$random1}.{$random2}";
    }

    /**
     * Send custom event to Google Analytics (GA4 Measurement Protocol)
     */
    public function track(array $data = [], ?string $eventName = null): bool
    {
        try {
            if (!$this->measurementId || !$this->apiSecret) {
                if (config("tracker.debug", false)) {
                    Log::warning('[LaravelTracker] GoogleAnalytics@track - Missing Measurement ID or API Secret');
                }
                return false;
            }

            // Use provided event name, fallback to config, or default to 'page_view'
            $eventName = $eventName ?? config('tracker.analytics.google.event_name') ?? 'page_view';
            
            // Use visitor_id as client_id for consistent tracking across sessions
            $clientId = $data['visitor_id'] ?? $this->generateClientId();

            $payload = [
                'client_id' => $clientId,
                'events' => [
                    [
                        'name' => $eventName,
                        'params' => array_merge($data, [
                            'engagement_time_msec' => 1,
                            'session_id' => $data['session_id'] ?? null,
                        ])
                    ]
                ]
            ];

            $response = \Illuminate\Support\Facades\Http::withQueryParameters([
                'measurement_id' => $this->measurementId,
                'api_secret'     => $this->apiSecret
            ])->post('https://www.google-analytics.com/mp/collect', $payload);

            if (config("tracker.debug", false)) {
                \Illuminate\Support\Facades\Log::debug('[LaravelTracker] GoogleAnalytics@track - Sent GA event', [
                    'measurement_id' => $this->measurementId,
                    'event_name'     => $eventName,
                    'client_id'      => $clientId,
                    'status'         => $response->status(),
                    'response'       => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[LaravelTracker] GoogleAnalytics@track - Failed to send GA event: ' . $e->getMessage());
            return false;
        }
    }
}


