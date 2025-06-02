<?php

namespace Souravmsh\LaravelTracker\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Souravmsh\LaravelTracker\Models\TrackerLog;

class IPApiService
{
    protected $ipApiEnabled;
    protected $ipApiToken;

    public function __construct()
    {
        $this->ipApiEnabled = config("tracker.analytics.ip_api.enabled", false);
        $this->ipApiToken   = config("tracker.analytics.ip_api.token");
    }

    public function track($request = [])
    {
        $ipAddress = $request["ip_address"] ?? request()->ip();
        $countryData = null;

        // Step 1: Get country data if IP API is enabled and IP is not private
        if ($this->ipApiEnabled && !$this->isPrivateIp($ipAddress)) {
            $countryData = $this->getCountryFromIp($ipAddress);
        }

        // Step 2: Update database with country information if available
        if ($countryData) {
            $this->updateTrackerRecord($request, $countryData);
        }
    }

    /**
     * Check if the IP address is private or reserved
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function isPrivateIp($ipAddress)
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Fetch country data from ipapi.co API with caching and retry logic
     *
     * @param string $ipAddress
     * @return array|null
     */
    protected function getCountryFromIp($ipAddress)
    {
        // Check cache first to reduce API calls
        $cacheKey = "ipapi_country_" . str_replace(".", "_", $ipAddress);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $maxRetries = 1;
        $retryDelay = 1; // Initial delay in seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $url = $this->ipApiToken ? "https://ipapi.co/{$ipAddress}/json/?key={$this->ipApiToken}" : "https://ipapi.co/{$ipAddress}/json/";

                $response = Http::get($url);
                $data = $response->json();

                if (isset($data["error"]) || empty($data["country_name"])) {
                    Log::warning("IP to country API failed", [
                        "ip_address" => $ipAddress,
                        "response" => $data,
                        "reason" => $data["reason"] ?? "Unknown",
                    ]);
                    return null;
                }

                $countryData = [
                    "ip_address"   => $ipAddress ?? null,
                    "country_code" => $data["country_code"] ?? null,
                    "country_name" => $data["country_name"] ?? null,
                    "address" => trim(
                        implode(
                            ", ",
                            array_filter([
                                $data["city"] ?? null,
                                $data["region"] ?? null,
                                $data["postal"] ?? null,
                            ])
                        )
                    ),
                    "country_geo" => isset(
                        $data["latitude"],
                        $data["longitude"]
                    )
                        ? "{$data["latitude"]},{$data["longitude"]}"
                        : null,
                    "country_flag" => $this->getCountryFlag(
                        $data["country_code"] ?? null
                    ),
                ];

                // Cache the result for 24 hours
                Cache::put($cacheKey, $countryData, now()->addHours(24));

                return $countryData;
            } catch (Exception $e) {
                if ($e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 429) {
                    Log::warning("[LaravelTracker]AnalyticService@track - IP API rate limit exceeded", [
                        "ip_address" => $ipAddress,
                        "attempt" => $attempt,
                        "error" => $e->getMessage(),
                    ]);

                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        $retryDelay *= 2; // Exponential backoff
                        continue;
                    }
                }

                Log::error("[LaravelTracker]AnalyticService@track - Failed to fetch country from IP", [
                    "ip_address" => $ipAddress,
                    "error" => $e->getMessage(),
                ]);
                return null;
            }
        }

        return null;
    }

    /**
     * Generate country flag emoji from country code
     *
     * @param string|null $countryCode
     * @return string|null
     */
    protected function getCountryFlag($countryCode)
    {
        if (!$countryCode) {
            return null;
        }

        $codePoints = array_map(function ($char) {
            return 0x1f1e6 + (ord(strtoupper($char)) - ord("A"));
        }, str_split($countryCode));

        return mb_convert_encoding(
            sprintf("&#x%X;&#x%X;", $codePoints[0], $codePoints[1]),
            "UTF-8",
            "HTML-ENTITIES"
        );
    }

    /**
     * Update the tracker record in the database with country data
     *
     * @param array $trackerData
     * @param array|null $countryData
     * @return void
     */
    protected function updateTrackerRecord($trackerData, $countryData)
    {
        try {
            $trackerId = $trackerData["visitor_id"] ?? null;

            if ($trackerId && $countryData) {
                TrackerLog::where("visitor_id", $trackerId)->update([
                    "ip_address"   => $countryData["ip_address"],
                    "country_name" => $countryData["country_name"],
                    "country_code" => $countryData["country_code"],
                    "country_flag" => $countryData["country_flag"],
                    "country_geo" => $countryData["country_geo"],
                    "address" => $countryData["address"],
                ]);
            }
        } catch (Exception $e) {
            Log::error("[LaravelTracker]AnalyticService@track - Failed to update tracker record", [
                "tracker_id" => $trackerId,
                "error" => $e->getMessage(),
            ]);
        }
    }
}
