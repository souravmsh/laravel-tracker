<?php

namespace Souravmsh\LaravelTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Souravmsh\LaravelTracker\Models\TrackerReferral;
use Souravmsh\LaravelTracker\Services\TrackerDataService;

class TrackerWebController extends Controller
{
    protected $view = "tracker::pages.";
    private $trackerDataService;

    public function __construct(TrackerDataService $trackerDataService) 
    {
        $this->trackerDataService = $trackerDataService;
    }

    public function dashboard(Request $request)
    {
        
        $report = $this->trackerDataService->getDashboardReport($request);

        return view($this->view . "dashboard", [
            "referrals"          => $report["referrals"],
            "visitors"           => $report["visitors"],
            "uniqueVisitorChart" => $report["uniqueVisitorChart"],
            "last30DaysChart"    => $report["last30DaysChart"],
            "sourceChart"        => $report["sourceChart"],
            "mediumTrendChart"   => $report["mediumTrendChart"],
            "campaignChart"      => $report["campaignChart"],
            "mostVisitedPages"   => $report["mostVisitedPages"],
            "totalVisitors"      => $report["totalVisitors"],
            "uniqueVisitors"     => $report["uniqueVisitors"],
            "totalRerferral"     => $report["totalRerferral"],
            "totalUniqueSource"  => $report["totalUniqueSource"],
        ]);
    }

    public function referrals(Request $request)
    {
        $request->validate([
            'referral_code' => 'nullable|string|max:255',
            'date_from'     => 'nullable|date_format:Y-m-d',
            'date_to'       => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'country_code'  => 'nullable|string|size:2', // Assuming ISO 3166-1 alpha-2 country codes
            'ip_address'    => 'nullable|ip',
        ]);

        return view($this->view . '.referrals', [
            "referrals" => $this->trackerDataService->getReferrals($request)
        ]);
    }

    public function visitors(Request $request)
    {
        // Validate request inputs
        $request->validate([
            'referral_code' => 'nullable|string|max:255',
            'date_from'     => 'nullable|date_format:Y-m-d',
            'date_to'       => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'country_code'  => 'nullable|string|size:2', // Assuming ISO 3166-1 alpha-2 country codes
            'ip_address'    => 'nullable|ip',
        ]);

        return view($this->view . '.visitors', [
            "visitors" => $this->trackerDataService->getVisitors($request)
        ]);
    }

    public function saveReferral(Request $request)
    {
        $request->validate([
            'id'          => 'nullable|string|max:11',
            'code'        => 'required|string|max:32',
            'title'       => 'required|string|max:128',
            'description' => 'nullable|string|max:255',
            'status'      => 'required|in:0,1',
            'position'    => 'required|min:0',
            'expires_at'  => 'required|date',
        ]);

        $save = $this->trackerDataService->saveReferral($request);
        if (!$save) {
            return response()->json(['success' => false, 'message' => 'Failed to save referral.'], 500);
        }

        return response()->json(['success' => true, 'referral' => $request->all()], 200);
    }
}
