<?php

namespace Souravmsh\LaravelTracker\Services;

use Illuminate\Support\Facades\DB;
use Souravmsh\LaravelTracker\Models\TrackerLog;
use Souravmsh\LaravelTracker\Models\TrackerReferral;

class TrackerDataService
{
    /**
     * Build a base TrackerLog query with common filters applied.
     */
    protected static function baseLogQuery($request)
    {
        return TrackerLog::when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address,   fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from,    fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to,      fn($q) => $q->whereDate("created_at", "<=", $request->date_to));
    }

    public static function getDashboardReport($request)
    {
        // --- Referrals with log counts ------------------------------------------
        $referralsQuery = TrackerReferral::withCount([
            'logs' => fn($q) => $q
                ->when($request->date_from,    fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
                ->when($request->date_to,      fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
                ->when($request->ip_address,   fn($q) => $q->where('ip_address', $request->ip_address))
                ->when($request->referral_code, fn($q) => $q->where('referral_code', $request->referral_code)),
        ]);

        if ($request->referral_code) {
            $referralsQuery->where("code", $request->referral_code);
        }
        $referrals = $referralsQuery->orderByDesc('logs_count')->get();

        // --- Summary stats (consolidated into one query) -------------------------
        $statsQuery = static::baseLogQuery($request);
        $stats = $statsQuery->selectRaw("
            COUNT(*) as total_visits,
            COUNT(DISTINCT visitor_id) as unique_visitors,
            COUNT(DISTINCT CASE WHEN referral_code IS NOT NULL THEN referral_code END) as total_referrals,
            COUNT(DISTINCT utm_source) as total_unique_sources
        ")->first();

        $totalVisitors     = (int) ($stats->total_visits ?? 0);
        $uniqueVisitors    = (int) ($stats->unique_visitors ?? 0);
        $totalRerferral    = (int) ($stats->total_referrals ?? 0);
        $totalUniqueSource = (int) ($stats->total_unique_sources ?? 0);

        // --- Latest visitors (simplified — most recent log per visitor) ----------
        $visitors = static::baseLogQuery($request)
            ->selectRaw("
                visitor_id,
                session_id,
                referral_code,
                utm_source,
                utm_medium,
                ip_address,
                MAX(visit_url)     as visit_url,
                MAX(referral_url)  as referral_url,
                MAX(country_name)  as country_name,
                MAX(country_code)  as country_code,
                MAX(country_flag)  as country_flag,
                MAX(created_at)    as created_at,
                COUNT(*)           as page_views
            ")
            ->groupBy("visitor_id", "session_id", "referral_code", "utm_source", "utm_medium", "ip_address")
            ->orderByDesc("created_at")
            ->limit(5)
            ->get();

        // --- Unique visitors per referral for chart ------------------------------
        $uniqueVisitorsPerReferral = TrackerLog::selectRaw("referral_code, COUNT(DISTINCT visitor_id) as unique_count")
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address,    fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from,     fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to,       fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->whereNotNull("referral_code")
            ->groupBy("referral_code")
            ->pluck("unique_count", "referral_code")
            ->toArray();

        $uniqueVisitorChart = [
            "labels"          => $referrals->pluck("code")->toArray(),
            "visits"          => $referrals->pluck("logs_count")->toArray(),
            "unique_visitors" => $referrals->map(fn($r) => $uniqueVisitorsPerReferral[$r->code] ?? 0)->toArray(),
        ];

        // --- Daily visitor trend (last 30 days or filtered range) ---------------
        $visitorChartData = static::baseLogQuery($request)
            ->when(!$request->date_from && !$request->date_to,
                fn($q) => $q->whereDate("created_at", ">=", now()->subDays(30))
            )
            ->selectRaw("DATE(created_at) as date, COUNT(*) as total_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->groupByRaw("DATE(created_at)")
            ->orderBy("date")
            ->get();

        $last30DaysChart = [
            "labels"      => $visitorChartData->pluck("date")->toArray(),
            "total_count" => $visitorChartData->pluck("total_count")->toArray(),
            "unique_count"=> $visitorChartData->pluck("unique_count")->toArray(),
        ];

        // --- UTM Source chart ----------------------------------------------------
        $sourceChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_source, COUNT(*) as visit_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_source")
            ->groupBy("utm_source")
            ->orderByDesc("visit_count")
            ->get();

        $sourceChart = [
            "labels"       => $sourceChartRaw->pluck("utm_source")->toArray(),
            "counts"       => $sourceChartRaw->pluck("visit_count")->toArray(),
            "unique_counts"=> $sourceChartRaw->pluck("unique_count")->toArray(),
        ];

        // --- UTM Medium chart ----------------------------------------------------
        $mediumChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_medium, COUNT(*) as visit_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_medium")
            ->groupBy("utm_medium")
            ->orderByDesc("visit_count")
            ->get();

        $mediumTrendChart = [
            "labels"   => $mediumChartRaw->pluck("utm_medium")->toArray(),
            "datasets" => [
                [
                    "label" => "Total Visits",
                    "data"  => $mediumChartRaw->pluck("visit_count")->toArray(),
                ],
                [
                    "label" => "Unique Visitors",
                    "data"  => $mediumChartRaw->pluck("unique_count")->toArray(),
                ],
            ],
        ];

        // --- UTM Campaign chart --------------------------------------------------
        $campaignChartRaw = static::baseLogQuery($request)
            ->selectRaw("utm_campaign, COUNT(*) as total_visits, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("utm_campaign")
            ->groupBy("utm_campaign")
            ->orderByDesc("total_visits")
            ->get();

        $campaignChart = [
            "labels"       => $campaignChartRaw->pluck("utm_campaign")->toArray(),
            "visits"       => $campaignChartRaw->pluck("total_visits")->toArray(),
            "unique_counts"=> $campaignChartRaw->pluck("unique_count")->toArray(),
        ];

        // --- Most visited pages -------------------------------------------------
        $mostVisitedPages = static::baseLogQuery($request)
            ->selectRaw("visit_url, COUNT(*) as visit_count, COUNT(DISTINCT visitor_id) as unique_count")
            ->whereNotNull("visit_url")
            ->groupBy("visit_url")
            ->orderByDesc("visit_count")
            ->limit(10)
            ->get();

        return [
            "referrals"          => $referrals,
            "visitors"           => $visitors,
            "uniqueVisitorChart" => $uniqueVisitorChart,
            "last30DaysChart"    => $last30DaysChart,
            "sourceChart"        => $sourceChart,
            "mediumTrendChart"   => $mediumTrendChart,
            "campaignChart"      => $campaignChart,
            "mostVisitedPages"   => $mostVisitedPages,
            "totalVisitors"      => $totalVisitors,
            "uniqueVisitors"     => $uniqueVisitors,
            "totalRerferral"     => $totalRerferral,
            "totalUniqueSource"  => $totalUniqueSource,
        ];
    }

    /**
     * Paginated visitors list, grouped per visitor/session, with visit counts.
     */
    public static function getVisitors($request)
    {
        return TrackerLog::selectRaw("
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
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->date_from,     fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to,       fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->when($request->country_code,  fn($q) => $q->where("country_code", $request->country_code))
            ->when($request->ip_address,    fn($q) => $q->where("ip_address", $request->ip_address))
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

    /**
     * Paginated referrals list with log counts.
     */
    public static function getReferrals($request)
    {
        return TrackerReferral::withCount("logs")
            ->when($request->title,  fn($q) => $q->where("title", "like", "%" . $request->title . "%"))
            ->when($request->code,   fn($q) => $q->where("code", "=", $request->code))
            ->when($request->status, fn($q) => $q->where("status", "=", $request->status))
            ->orderByDesc("id")
            ->paginate(15);
    }

    /**
     * Create or update a referral record.
     */
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
