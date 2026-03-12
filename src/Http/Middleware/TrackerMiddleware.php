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
        $allowedPaths = config("tracker.allowed_paths", []);

        // Basic exclusions
        if ($request->expectsJson() || !$request->acceptsHtml() || $request->isXmlHttpRequest() || $request->isMethod("post")) {
            return $next($request);
        }

        // Check ignored paths
        if ($request->is($this->ignorePaths)) {
            return $next($request);
        }

        // Check allowed paths (if defined)
        if (!empty($allowedPaths) && !$request->is($allowedPaths)) {
            return $next($request);
        }
        
        if (config("tracker.enabled", true)) {
            $this->trackerMiddlewareService->track($request);
        }

        return $next($request);
    }
}
