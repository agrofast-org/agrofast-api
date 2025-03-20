<?php

namespace App\Http\Middleware;

use App\Models\Hr\BrowserAgent;
use Symfony\Component\HttpFoundation\Response;

class BrowserAgentMiddleware
{
    public function handle($request, \Closure $next)
    {
        $browserAgent = $request->header('Browser-Agent');

        if (env('APP_ENV') !== 'local') {
            return $next($request);
        }

        if (!$browserAgent) {
            return response()->json(['message' => 'no_browser_agent_provided', 'code' => 'browser_agent'], Response::HTTP_UNAUTHORIZED);
        }

        $storedBrowserAgent = BrowserAgent::where('fingerprint', $browserAgent)->first();

        if (!$storedBrowserAgent) {
            return response()->json(['message' => 'invalid_browser_agent', 'code' => 'browser_agent'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
