<?php

namespace Souravmsh\LaravelTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Souravmsh\LaravelTracker\Models\TrackerReferral;
use Souravmsh\LaravelTracker\Services\TrackerDataService;
use Souravmsh\LaravelTracker\Services\TrackerSettingService;

class TrackerWebController extends Controller
{
    protected $view = "tracker::pages.";
    private $trackerDataService;
    private $settingService;

    public function __construct(TrackerDataService $trackerDataService, TrackerSettingService $settingService)
    {
        $this->trackerDataService = $trackerDataService;
        $this->settingService     = $settingService;
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

    /**
     * Show the settings management page.
     */
    public function settings()
    {
        $settings = $this->settingService->grouped();
        return view($this->view . 'settings', compact('settings'));
    }

    /**
     * Save updated settings and flush cache.
     */
    public function saveSettings(Request $request)
    {
        $allowed = [
            'enabled', 'debug', 'queue_enabled', 'log_to_database',
            'rate_limit', 'session_lifetime', 'max_input_length',
            'ip_api_enabled', 'ip_api_token',
            'ga_enabled', 'ga_measurement_id', 'ga_api_secret', 'ga_event_name',
            'referral_code_params', 'ignore_paths', 'allowed_paths', 'layout',
            'title', 'cache_ttl', 'route_prefix', 'route_middleware',
        ];

        $data = [];
        foreach ($allowed as $key) {
            // Checkboxes: unchecked = not present in request; treat as 0
            if (in_array($key, ['enabled', 'debug', 'queue_enabled', 'log_to_database', 'ip_api_enabled', 'ga_enabled'])) {
                $data[$key] = $request->has($key) ? '1' : '0';
            } else if (in_array($key, ['referral_code_params', 'ignore_paths', 'allowed_paths'])) {
                $val = $request->input($key);
                if ($val && !str_starts_with(trim($val), '[')) {
                    // Convert comma separated to JSON
                    $data[$key] = json_encode(array_map('trim', explode(',', $val)));
                } else {
                    $data[$key] = $val;
                }
            } else {
                $data[$key] = $request->input($key);
            }
        }

        $this->settingService->saveMany($data);

        return redirect()->route('tracker.settings')->with('success', 'Settings saved successfully.');
    }
}
