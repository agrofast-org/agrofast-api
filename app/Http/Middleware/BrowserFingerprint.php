<?php

namespace App\Http\Middleware;

use App\Factories\ResponseFactory;
use App\Models\Hr\BrowserAgent;

class BrowserFingerprint
{
    public function handle($request, \Closure $next)
    {
        $browserAgent = $request->header('Browser-Agent');

        if (!$browserAgent) {
            return ResponseFactory::error('no_browser_agent_provided', ['code' => 'browser_agent'], null, 401);
        }

        $storedBrowserAgent = BrowserAgent::where('fingerprint', $browserAgent)->first();

        if (!$storedBrowserAgent) {
            return ResponseFactory::error('invalid_browser_agent', ['code' => 'browser_agent'], null, 401);
        }

        return $next($request);
    }
}
