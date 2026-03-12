<?php

namespace Souravmsh\LaravelTracker\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Souravmsh\LaravelTracker\Models\TrackerLog;
use Souravmsh\LaravelTracker\Models\TrackerReferral;

class TrackerDataService
{
    /**
     * Build a base TrackerLog query with common filters applied.
     * Uses timestamp comparisons instead of whereDate for index utilization.
     */
    protected static function baseLogQuery($request)
    {
        $query = TrackerLog::query();

        if ($request->referral_code) {
            $query->where("referral_code", $request->referral_code);
        }
        
        if ($request->ip_address) {
            $query->where("ip_address", $request->ip_address);
        }
        
        if ($request->date_from) {
            $query->where("created_at", ">=", $request->date_from . " 00:00:00");
        }
        
        if ($request->date_to) {
            $query->where("created_at", "<=", $request->date_to . " 23:59:59");
        }

        return $query;
    }

    public static function getDashboardReport($request)
    {
        $cacheTtl = config('tracker.cache_ttl', 300);
        $cacheKey = 'tracker_dashboard_report_' . md5(json_encode($request->all()));

        if ($cacheTtl > 0 && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // --- Summary stats (consolidated into one query) -------------------------
        $stats = static::baseLogQuery($request)
            ->selectRaw("
                COUNT(*) as total_visits,
                COUNT(DISTINCT visitor_id) as unique_visitors,
                COUNT(DISTINCT CASE WHEN referral_code IS NOT NULL THEN referral_code END) as total_referrals,
                COUNT(DISTINCT utm_source) as total_unique_sources
            ")->first();

        // --- Referrals with log counts ------------------------------------------
        $referralsQuery = TrackerReferral::withCount([
            'logs' => function($q) use ($request) {
                if ($request->date_from) $q->where('created_at', '>=', $request->date_from . " 00:00:00");
                if ($request->date_to)   $q->where('created_at', '<=', $request->date_to . " 23:59:59");
                if ($request->ip_address) $q->where('ip_address', $request->ip_address);
                if ($request->referral_code) $q->where('referral_code', $request->referral_code);
            },
        ]);

        if ($request->referral_code) {
            $referralsQuery->where("code", $request->referral_code);
        }
        $referrals = $referralsQuery->orderByDesc('logs_count')->get();

        // --- Latest visitors (grouped optimally) --------------------------------
        $visitors = static::baseLogQuery($request)
            ->select('visitor_id', 'session_id', 'referral_code', 'utm_source', 'utm_medium', 'ip_address', 'country_name', 'country_code', 'country_flag', 'visit_url', 'referral_url', 'created_at')
            ->latest()
            ->limit(5)
            ->get();

        // --- Unique visitors per referral for chart ------------------------------
        $uniqueVisitorsPerReferral = static::baseLogQuery($request)
            ->whereNotNull("referral_code")
            ->selectRaw("referral_code, COUNT(DISTINCT visitor_id) as unique_count")
            ->groupBy("referral_code")
            ->pluck("unique_count", "referral_code")
            ->toArray();

        // --- Daily visitor trend (last 30 days or filtered range) ---------------
        $trendQuery = static::baseLogQuery($request);
        if (!$request->date_from && ! $request->date_to) {
            $trendQuery->where("created_at", ">=", now()->subDays(30)->startOfDay());
        }
        
        $visitorChartData = $trendQuery->selectRaw("DATE(created_at) as date, COUNT(*) as total_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->groupByRaw("DATE(created_at)")
            ->orderBy("date")
            ->get();

        // --- UTM Distribution Data (Consolidated) --------------------------------
        $sourceChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_source, COUNT(*) as visit_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_source")
            ->groupBy("utm_source")
            ->orderByDesc("visit_count")
            ->get();

        $mediumChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_medium, COUNT(*) as visit_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_medium")
            ->groupBy("utm_medium")
            ->orderByDesc("visit_count")
            ->get();

        $campaignChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_campaign, COUNT(*) as total_visits, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_campaign")
            ->groupBy("utm_campaign")
            ->orderByDesc("total_visits")
            ->get();

        // --- Most visited pages -------------------------------------------------
        $mostVisitedPages = static::baseLogQuery($request)
            ->selectRaw("visit_url, COUNT(*) as visit_count")
            ->whereNotNull("visit_url")
            ->groupBy("visit_url")
            ->orderByDesc("visit_count")
            ->limit(10)
            ->get();

        $report = [
            "referrals"          => $referrals,
            "visitors"           => $visitors,
            "uniqueVisitorChart" => [
                "labels"          => $referrals->pluck("code")->toArray(),
                "visits"          => $referrals->pluck("logs_count")->toArray(),
                "unique_visitors" => $referrals->map(fn($r) => $uniqueVisitorsPerReferral[$r->code] ?? 0)->toArray(),
            ],
            "last30DaysChart"    => [
                "labels"      => $visitorChartData->pluck("date")->toArray(),
                "total_count" => $visitorChartData->pluck("total_count")->toArray(),
                "unique_count"=> $visitorChartData->pluck("unique_count")->toArray(),
            ],
            "sourceChart"        => [
                "labels"       => $sourceChartRaw->pluck("utm_source")->toArray(),
                "counts"       => $sourceChartRaw->pluck("visit_count")->toArray(),
                "unique_counts"=> $sourceChartRaw->pluck("unique_count")->toArray(),
            ],
            "mediumTrendChart"   => [
                "labels"   => $mediumChartRaw->pluck("utm_medium")->toArray(),
                "datasets" => [
                    ["label" => "Total Visits", "data" => $mediumChartRaw->pluck("visit_count")->toArray()],
                    ["label" => "Unique Visitors", "data" => $mediumChartRaw->pluck("unique_count")->toArray()],
                ],
            ],
            "campaignChart"      => [
                "labels"       => $campaignChartRaw->pluck("utm_campaign")->toArray(),
                "visits"       => $campaignChartRaw->pluck("total_visits")->toArray(),
                "unique_counts"=> $campaignChartRaw->pluck("unique_count")->toArray(),
            ],
            "mostVisitedPages"   => $mostVisitedPages,
            "totalVisitors"      => (int) ($stats->total_visits ?? 0),
            "uniqueVisitors"     => (int) ($stats->unique_visitors ?? 0),
            "totalRerferral"     => (int) ($stats->total_referrals ?? 0),
            "totalUniqueSource"  => (int) ($stats->total_unique_sources ?? 0),
        ];

        if ($cacheTtl > 0) {
            Cache::put($cacheKey, $report, now()->addSeconds($cacheTtl));
        }

        return $report;
    }

    public static function getVisitors($request)
    {
        return static::baseLogQuery($request)
            ->selectRaw("
                visitor_id,
                session_id,
                ip_address,
                referral_code,
                user_id,
                utm_source,
                utm_medium,
                utm_campaign,
                country_code,
                MAX(visit_url)    as visit_url,
                MAX(referral_url) as referral_url,
                MAX(country_name) as country_name,
                MAX(country_flag) as country_flag,
                MAX(country_geo)  as country_geo,
                MAX(address)      as address,
                MAX(user_agent)   as user_agent,
                MAX(created_at)   as created_at,
                COUNT(*)          as page_views
            ")
            ->groupBy(
                "visitor_id",
                "session_id",
                "ip_address",
                "referral_code",
                "user_id",
                "utm_source",
                "utm_medium",
                "utm_campaign",
                "country_code"
            )
            ->orderByDesc("created_at")
            ->paginate($request->input("per_page", 15));
    }

    public static function getReferrals($request)
    {
        return TrackerReferral::withCount("logs")
            ->when($request->title,  fn($q) => $q->where("title", "like", "%" . $request->title . "%"))
            ->when($request->code,   fn($q) => $q->where("code", "=", $request->code))
            ->when($request->status, fn($q) => $q->where("status", "=", $request->status))
            ->orderByDesc("id")
            ->paginate(15);
    }

    public static function saveReferral($request)
    {
        $data = [
            "code"        => $request->code ?? null,
            "title"       => $request->title ?? null,
            "description" => $request->description ?? null,
            "status"      => $request->status ?? null,
            "position"    => $request->position ?? null,
            "expires_at"  => $request->expires_at ? now()->parse($request->expires_at)->format("Y-m-d H:i:s") : null,
            "updated_by"  => $request->user()?->id,
            "updated_at"  => now(),
        ];

        if (!$request->id) {
            $data["created_by"] = $request->user()?->id;
            $data["created_at"] = now();
        }

        return TrackerReferral::updateOrCreate(
            ["id" => $request->id],
            $data
        );
    }
}
