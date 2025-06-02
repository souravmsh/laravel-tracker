<?php

namespace Souravmsh\LaravelTracker\Services;

use Souravmsh\LaravelTracker\Models\TrackerLog;
use Souravmsh\LaravelTracker\Models\TrackerReferral;

class TrackerDataService
{
    public static function getDashboardReport($request)
    {
        $referralsQuery = TrackerReferral::withCount([
            'logs' => function ($query) use ($request) {
                $query->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
                    ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
                    ->when($request->ip_address, fn($q) => $q->where('ip_address', $request->ip_address))
                    ->when($request->referral_code, fn($q) => $q->where('referral_code', $request->referral_code));
            }
        ]);

        if ($request->referral_code) {
            $referralsQuery->where("code", $request->referral_code);
        }
        $referrals = $referralsQuery->get();

        $visitors = TrackerLog::selectRaw("visitor_id, referral_code, MAX(created_at) as created_at, utm_source, MAX(country_name) AS country_name, MAX(country_code) AS country_code, MAX(country_flag) AS country_flag, utm_medium, COUNT(*) as visits")
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->groupBy( "created_at", "visitor_id", "referral_code", "utm_source", "utm_medium")
            ->orderBy("created_at", "desc")
            ->latest()
            ->take(5)
            ->get();
 
        $uniqueVisitorsPerReferral = TrackerLog::selectRaw('referral_code, COUNT(DISTINCT visitor_id) as unique_visitor_count')
            ->when($request->referral_code, fn($q) => $q->where('referral_code', $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where('ip_address', $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->groupBy('referral_code')
            ->pluck("unique_visitor_count", "referral_code")
            ->toArray();

        $uniqueVisitorChart = [
            "labels" => $referrals->pluck("code")->toArray(),
            "visits" => $referrals->pluck("logs_count")->toArray(),
            "unique_visitors" => $referrals
                ->map(fn($referral) => $uniqueVisitorsPerReferral[$referral->code] ?? 0)
                ->toArray(),
        ];

        $visitorChartData = TrackerLog::selectRaw('DATE(created_at) as date, COUNT(DISTINCT visitor_id) as unique_visitor_count, COUNT(visitor_id) as total_count')
            ->when($request->referral_code, fn($q) => $q->where('referral_code', $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where('ip_address', $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when(!$request->date_from && !$request->date_to, fn($q) => $q->whereDate('created_at', '>=', now()->subDays(30)))
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                "date" => $item->date,
                "total_count" => $item->total_count,
                "unique_count" => $item->unique_visitor_count,
            ]);

        $last30DaysChart = [
            "labels" => $visitorChartData->pluck("date")->toArray(),
            "total_count" => $visitorChartData->pluck("total_count")->toArray(),
            "unique_count" => $visitorChartData->pluck("unique_count")->toArray(),
        ];

        $sourceChartRaw = TrackerLog::selectRaw('utm_source, COUNT(*) as visitor_count')
            ->whereNotNull("utm_source")
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->groupBy("utm_source")
            ->get();

        $sourceChart = [
            "labels" => $sourceChartRaw->pluck("utm_source")->toArray(),
            "counts" => $sourceChartRaw->pluck("visitor_count")->toArray(),
        ];

        $mediumChartRaw = TrackerLog::selectRaw('utm_medium, COUNT(*) as visitor_count')
            ->whereNotNull("utm_medium")
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->groupBy("utm_medium")
            ->get();

        $mediumTrendChart = [
            "labels" => $mediumChartRaw->pluck("utm_medium")->toArray(),
            "datasets" => [
                [
                    "label" => "Visitors by Medium",
                    "data" => $mediumChartRaw->pluck("visitor_count")->toArray(),
                ],
            ],
        ];

        $campaignChartRaw = TrackerLog::selectRaw('utm_campaign, COUNT(*) as total_visits')
            ->whereNotNull("utm_campaign")
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->groupBy("utm_campaign")
            ->get();

        $campaignChart = [
            "labels" => $campaignChartRaw->pluck("utm_campaign")->toArray(),
            "visits" => $campaignChartRaw->pluck("total_visits")->toArray(),
        ];

        $mostVisitedPages = TrackerLog::selectRaw('visit_url, COUNT(*) as visit_count')
            ->when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to))
            ->groupBy("visit_url")
            ->orderBy("visit_count", "desc")
            ->take(5)
            ->get();


        $totalVisitorsQuery = TrackerLog::when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to));

        $totalVisitors = $totalVisitorsQuery->count();
        $uniqueVisitors = $totalVisitorsQuery->distinct("visitor_id")->count();
        $totalUniqueSource = $totalVisitorsQuery->distinct("utm_source")->count();

        $totalReferralQuery = TrackerLog::when($request->referral_code, fn($q) => $q->where("referral_code", $request->referral_code))
            ->when($request->ip_address, fn($q) => $q->where("ip_address", $request->ip_address))
            ->when($request->date_from, fn($q) => $q->whereDate("created_at", ">=", $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate("created_at", "<=", $request->date_to));

        $totalRerferral = $totalReferralQuery->count("referral_code");

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

    public static function getVisitors($request)
    {
        return TrackerLog::selectRaw("
            visitor_id,
            visit_url,
            ip_address,
            referral_code,
            user_id,
            utm_source,
            utm_medium,
            utm_campaign,
            country_code,
            MAX(referral_url) as referral_url,
            MAX(country_name) as country_name,
            MAX(country_flag) as country_flag,
            MAX(country_geo) as country_geo,
            MAX(address) as address,
            MAX(user_agent) as user_agent,
            MAX(created_at) as created_at,
            COUNT(visit_url) as visits
        ")
            ->when($request->referral_code, fn($query) => $query->where('referral_code', $request->referral_code))
            ->when($request->date_from, fn($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->when($request->country_code, fn($query) => $query->where('country_code', $request->country_code))
            ->when($request->ip_address, fn($query) => $query->where('ip_address', $request->ip_address))
            ->groupBy(
                'visitor_id',
                'visit_url',
                'ip_address',
                'referral_code',
                'user_id',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'country_code'
            )
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));
    }

    public static function getReferrals($request)
    {
        return TrackerReferral::withCount("logs")
            ->when($request->title, fn($query) => $query->where("title", "like", "%" . $request->title . "%"))
            ->when($request->code, fn($query) => $query->where("code", "=", $request->code))
            ->when($request->status, fn($query) => $query->where("status", "=", $request->status))
            ->orderBy("id", "desc")
            ->paginate(10);
    }

    public static function saveReferral($request)
    {
        $data = [
            'code'        => $request->code ?? null,
            'title'       => $request->title ?? null,
            'description' => $request->description ?? null,
            'status'      => $request->status ?? null,
            'position'    => $request->position ?? null,
            'expires_at'  => $request->expires_at ? now()->parse($request->expires_at)->format('Y-m-d H:i:s') : null,
            'updated_by'  => $request->user()->id ?? null,
            'updated_at'  => now(),
        ];

        // Only set created_by and created_at for new records
        if (!$request->id) {
            $data['created_by'] = $request->user()->id ?? null;
            $data['created_at'] = now();
        }

        $referral = TrackerReferral::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        return $referral;
    }

}
