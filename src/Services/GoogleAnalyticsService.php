<?php

namespace Souravmsh\LaravelTracker\Services;

use Exception;
use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_ReportRequest;
use Illuminate\Support\Facades\Cache;
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

        if (!$this->measurementId || !$this->apiSecret) {
            throw new Exception('Google Analytics configuration is missing.');
        }
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
     * Send custom event to Google Analytics
     */
    public function track(array $params = [], ?string $eventName = null): bool
    {
        try {
            // Use provided event name or fallback to 'page_view'
            $eventName = $eventName ?? 'page_view';

            // Generate client_id for this event
            $clientId = $this->generateClientId();

            $payload = [
                'client_id' => $clientId,
                'events' => [
                    [
                        'name' => $eventName,
                        'params' => $params
                    ]
                ]
            ];

            // Add query parameters to the POST request
            $response = Http::withQueryParameters([
                'measurement_id' => $this->measurementId,
                'api_secret'     => $this->apiSecret
            ])->post('https://www.google-analytics.com/mp/collect', $payload);

            if (config("tracker.debug", false)) {
                Log::debug('[LaravelTracker]GoogleAnalytics@track - Sent GA event', [
                    'measurement_id' => $this->measurementId,
                    'event_name'     => $eventName,
                    'client_id'      => $clientId,
                    'params'         => $params,
                    'response'       => $response->body(),
                ]);
            }

            return $response->status() === 204;
        } catch (Exception $e) {
            Log::error('[LaravelTracker]GoogleAnalytics@track - Failed to send GA event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize Google Analytics Reporting API client
     */
    protected function initializeAnalyticsReporting()
    {
        try {
            $client = new Google_Client();
            $client->setApplicationName(env('APP_NAME', 'Your Laravel App')); // Get app name from .env
            $client->setScopes([Google_Service_AnalyticsReporting::ANALYTICS_READONLY]);
            $client->setAuthConfig(config('tracker.analytics.google.credentials_path'));
            $this->analytics = new Google_Service_AnalyticsReporting($client);
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Analytics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get page views for dashboard
     */
    public function getPageViews(string $startDate = '7daysAgo', string $endDate = 'today'): array
    {
        try {
            $cacheKey = 'ga_page_views_' . md5($startDate . $endDate);

            return Cache::remember($cacheKey, now()->addHours(1), function () use ($startDate, $endDate) {
                $this->initializeAnalyticsReporting(); // Ensure analytics is initialized

                $dateRange = new Google_Service_AnalyticsReporting_DateRange();
                $dateRange->setStartDate($startDate);
                $dateRange->setEndDate($endDate);

                $pageViews = new Google_Service_AnalyticsReporting_Metric();
                $pageViews->setExpression('ga:pageviews');
                $pageViews->setAlias('pageviews');

                $pagePath = new Google_Service_AnalyticsReporting_Dimension();
                $pagePath->setName('ga:pagePath');

                $request = new Google_Service_AnalyticsReporting_ReportRequest();
                $request->setViewId(config('tracker.analytics.google.view_id'));
                $request->setDateRanges([$dateRange]);
                $request->setMetrics([$pageViews]);
                $request->setDimensions([$pagePath]);

                $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                $body->setReportRequests([$request]);

                $reports = $this->analytics->reports->batchGet($body);
                $data = [];

                foreach ($reports->getReports()[0]->getData()->getRows() as $row) {
                    $data[] = [
                        'page' => $row->getDimensions()[0],
                        'views' => $row->getMetrics()[0]->getValues()[0]
                    ];
                }

                return $data;
            });
        } catch (Exception $e) {
            Log::error('Failed to fetch page views: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active users for dashboard
     */
    public function getActiveUsers(string $startDate = '7daysAgo', string $endDate = 'today'): int
    {
        try {
            $cacheKey = 'ga_active_users_' . md5($startDate . $endDate);

            return Cache::remember($cacheKey, now()->addHours(1), function () use ($startDate, $endDate) {
                $this->initializeAnalyticsReporting(); // Ensure analytics is initialized

                $dateRange = new Google_Service_AnalyticsReporting_DateRange();
                $dateRange->setStartDate($startDate);
                $dateRange->setEndDate($endDate);

                $activeUsers = new Google_Service_AnalyticsReporting_Metric();
                $activeUsers->setExpression('ga:users');
                $activeUsers->setAlias('active_users');

                $request = new Google_Service_AnalyticsReporting_ReportRequest();
                $request->setViewId(config('tracker.analytics.google.view_id'));
                $request->setDateRanges([$dateRange]);
                $request->setMetrics([$activeUsers]);

                $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
                $body->setReportRequests([$request]);

                $reports = $this->analytics->reports->batchGet($body);
                return (int) $reports->getReports()[0]->getData()->getTotals()[0]->getValues()[0];
            });
        } catch (Exception $e) {
            Log::error('Failed to fetch active users: ' . $e->getMessage());
            return 0;
        }
    }
}
