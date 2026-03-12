<?php

namespace Souravmsh\LaravelTracker\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
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
            // Generate or retrieve visitor ID (persistent across sessions)
            $visitorId = $this->getVisitorId($request);

            // Session ID: identifies a browser session (not page-specific)
            $sessionId = $this->getSessionId();

            // Path-specific tracking key to prevent double-counting same page in same session
            $trackingKey = "tracker_visited:{$sessionId}:" . md5($request->path());

            // If this exact page was already tracked in this session, skip
            if (Cache::has($trackingKey)) {
                return;
            }

            // Rate limit using visitor IP
            $rateLimitKey = "tracker_rate:{$request->ip()}";
            $maxAttempts  = config("tracker.rate_limit", 300);
            if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
                Log::warning("[LaravelTracker] TrackerMiddlewareService@track - Rate limit exceeded", [
                    "ip_address" => $request->ip(),
                    "visitor_id" => $visitorId,
                ]);
                return;
            }

            // Collect tracker data
            $trackerData = [
                "visitor_id"    => $visitorId,
                "session_id"    => $sessionId,
                "referral_code" => $this->getReferralCode($request),
                "referral_url"  => $this->getReferer($request),
                "visit_url"     => $this->sanitizeInput($request->path()),
                "utm_source"    => $this->sanitizeInput($request->query("utm_source")),
                "utm_medium"    => $this->sanitizeInput($request->query("utm_medium")),
                "utm_campaign"  => $this->sanitizeInput($request->query("utm_campaign")),
                "ip_address"    => $request->ip(),
                "country_name"  => "Unknown",
                "user_agent"    => $this->sanitizeInput($request->header("User-Agent")),
                "user_id"       => $request->user()?->id,
                "created_at"    => now(),
            ];

            // Flag if geocoding is needed (only once per session)
            $needsGeo = config("tracker.analytics.ip_api.enabled") && !Session::has("tracker_geo_tracked");
            if ($needsGeo) {
                $trackerData["_needs_geo"] = true;
                Session::put("tracker_geo_tracked", true);
            }

            // Mark this page as visited within the session (TTL: 30 minutes)
            Cache::put($trackingKey, true, now()->addMinutes(30));
            RateLimiter::hit($rateLimitKey, 60);

            // Store session data for downstream use
            Session::put("tracker_data", $trackerData);

            // Log to database
            if (config("tracker.log_to_database", true)) {
                if (config("tracker.queue_enabled", true)) {
                    TrackerJob::dispatch($trackerData);
                } else {
                    TrackerLog::create($trackerData);
                    // Dispatch geocoding immediately if sync
                    if ($needsGeo) {
                        event(new IpApiEvent($trackerData));
                    }
                }
            }

            // Fire Google Analytics event
            if (config("tracker.analytics.google.enabled", false)) {
                event(new GoogleAnalyticsEvent($trackerData));
            }

        } catch (Exception $e) {
            Log::error("[LaravelTracker] TrackerMiddlewareService@track - error", [
                "error"      => $e->getMessage(),
                "ip_address" => $request->ip(),
                "visitor_id" => $visitorId ?? "unknown",
            ]);
        }
    }

    /**
     * Retrieve or create a persistent visitor UUID stored in a cookie.
     */
    protected function getVisitorId(Request $request): string
    {
        $visitorId = $request->cookie("tracker_visitor_id") ?? Session::get("tracker_visitor_id");

        if (!$visitorId) {
            $visitorId = (string) Str::uuid();
            Session::put("tracker_visitor_id", $visitorId);
            Cookie::queue("tracker_visitor_id", $visitorId, 60 * 24 * 365); // 1 year
        }

        return $visitorId;
    }

    /**
     * Retrieve or create a session-scoped UUID (persists for the browser session).
     */
    protected function getSessionId(): string
    {
        $sessionId = Session::get("tracker_session_id");

        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            Session::put("tracker_session_id", $sessionId);
        }

        return $sessionId;
    }

    /**
     * Extract a valid referral code from the request query string.
     */
    protected function getReferralCode(Request $request): ?string
    {
        $params = config("tracker.referral_code_params", ["ref", "code", "referral_code", "_rf"]);

        foreach ($params as $param) {
            if ($code = $this->sanitizeInput($request->query($param))) {
                if (preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $code)) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * Get the HTTP referrer URL from the request.
     */
    protected function getReferer(Request $request): ?string
    {
        return $this->sanitizeInput(
            $request->header("referer") ?? ($_SERVER["HTTP_REFERER"] ?? null)
        );
    }

    /**
     * Strip tags and limit input length.
     */
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