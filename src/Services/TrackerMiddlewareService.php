<?php

namespace Souravmsh\LaravelTracker\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Exception;
use Souravmsh\LaravelTracker\Events\IpApiEvent;
use Souravmsh\LaravelTracker\Events\GoogleAnalyticsEvent;
use Souravmsh\LaravelTracker\Jobs\TrackerJob;
use Souravmsh\LaravelTracker\Models\TrackerLog;

class TrackerMiddlewareService
{
    public function track(Request $request): void
    {
        try {
            // Generate or retrieve visitor ID and session ID
            $visitorId = $this->getVisitorId($request);
            $sessionId = md5($request->path() . $visitorId);

            // Create a unique key for tracking this visitor/session
            $trackingKey = "tracker_key_{$visitorId}{$sessionId}";

            // Check if already tracked in session or cache
            if (Session::has($trackingKey) || Cache::has($trackingKey)) {
                return;
            }

            // Rate limit using Redis
            $rateLimitKey = "referral-log:" . $request->ip();
            $maxAttempts = config("tracker.rate_limit", 5);
            if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
                Log::warning("[LaravelTracker]TrackerMiddlewareService@track( - Rate limit exceeded for referral tracking", [
                    "ip_address" => $request->ip(),
                    "visitor_id" => $visitorId,
                ]);
                return;
            }

            // Collect referral data
            $trackerData = [
                "visitor_id"    => $visitorId,
                "session_id"    => $sessionId,
                "referral_code" => $this->getReferralCode($request),
                "referral_url"  => $this->getReferer($request),
                "visit_url"     => $request->path(),
                "utm_source"    => $this->sanitizeInput($request->query("utm_source")),
                "utm_medium"    => $this->sanitizeInput($request->query("utm_medium")),
                "utm_campaign"  => $this->sanitizeInput($request->query("utm_campaign")),
                "ip_address"    => $request->ip(),
                "country_name"  => "Unknown",
                "user_agent"    => $this->sanitizeInput($request->header("User-Agent")),
                "user_id"       => $request->user->id ?? null,
                "created_at"    => now(),
            ];

            // Mark as tracked in session
            Session::put($trackingKey, true);
            Session::put("tracker_data", $trackerData);

            // Check for existing record in database to prevent duplicates
            if (config("tracker.log_to_database", true)) {
                if (config("tracker.queue_enabled", true)) {
                    TrackerJob::dispatch($trackerData);
                } else {
                    TrackerLog::create($trackerData);
                    RateLimiter::hit($rateLimitKey, 60);
                }
            }
            
            // Fire event for IP API tracking
            if (config("tracker.analytics.ip_api.enabled")) {
                event(new IpApiEvent($trackerData));
            }
            
            // Fire event for google analytics
            if (config("tracker.analytics.google.enabled", false)) {
                event(new GoogleAnalyticsEvent($trackerData));
            }

        } catch (Exception $e) {
            Log::error("[LaravelTracker]TrackerMiddlewareService@track - error", [
                "error"      => $e->getMessage(),
                "ip_address" => $request->ip(),
                "visitor_id" => $visitorId ?? "unknown",
            ]);
        }
    }

    protected function getVisitorId(Request $request): string
    {
        $visitorId = Session::get("visitor_id");
        if (!$visitorId) {
            $visitorId = md5($request->ip());
            Session::put("visitor_id", $visitorId);
            Session::save();
        }
        return $visitorId;
    }

    protected function getReferralCode(Request $request): ?string
    {
        $params = config("tracker.referral_code_params", [
            "ref",
            "code",
            "referral_code",
            "_rf",
        ]);
        foreach ($params as $param) {
            if ($code = $this->sanitizeInput($request->query($param))) {
                if (preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $code)) {
                    return $code;
                }
            }
        }
        return null;
    }

    protected function getReferer(Request $request): ?string
    {
        return $this->sanitizeInput(
            $request->header("referer") ?? ($_SERVER["HTTP_REFERER"] ?? null)
        );
    }

    protected function sanitizeInput($input): ?string
    {
        if (is_null($input)) {
            return null;
        }
        return Str::limit(
            strip_tags($input),
            config("tracker.max_input_length", 255)
        );
    }
}