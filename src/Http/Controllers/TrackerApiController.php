<?php

namespace Souravmsh\LaravelTracker\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Souravmsh\LaravelTracker\Models\TrackerReferral;
use Souravmsh\LaravelTracker\Models\trackerLog;

class TrackerApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $result = TrackerReferral::query()
            ->withCount("logs")
            ->when(
                $request->query("status"),
                fn($q, $status) => $q->where("status", $status)
            )
            ->when(
                $request->query("search"),
                fn($q, $search) => $q->where("title", "like", "%{$search}%")
            )
            ->paginate(20);

        return response()->json($result);
    }

    public function stats(Request $request): JsonResponse
    {
        $result = TrackerLog::query()
            ->selectRaw(
                "referral_code, COUNT(*) as visits, COUNT(DISTINCT visitor_id) as unique_visitors"
            )
            ->groupBy("referral_code")
            ->when(
                $request->query("date_from"),
                fn($q, $date) => $q->whereDate("created_at", ">=", $date)
            )
            ->when(
                $request->query("date_to"),
                fn($q, $date) => $q->whereDate("created_at", "<=", $date)
            )
            ->get();

        return response()->json($result);
    }
}
