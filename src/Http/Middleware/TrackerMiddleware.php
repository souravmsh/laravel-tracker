<?php

namespace Souravmsh\LaravelTracker\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Souravmsh\LaravelTracker\Services\TrackerMiddlewareService;

class TrackerMiddleware
{
    protected $trackerMiddlewareService;
    protected $ignorePaths = [];

    public function __construct(TrackerMiddlewareService $trackerMiddlewareService)
    {
        $this->trackerMiddlewareService = $trackerMiddlewareService;
        $this->ignorePaths = config("tracker.ignore_paths", []);
    }

    public function handle(Request $request, Closure $next)
    {
        // Skip non-HTML and asset requests
        if ($request->expectsJson() || !$request->acceptsHtml() || $request->isXmlHttpRequest() || $request->isMethod("post") || $request->is($this->ignorePaths)) {
            return $next($request);
        }
        
        if (config("tracker.enabled", true)) {
            $this->trackerMiddlewareService->track($request);
        }

        return $next($request);
    }
}
