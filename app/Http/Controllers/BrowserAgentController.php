<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Models\Hr\BrowserAgent;
use Illuminate\Http\JsonResponse;

class BrowserAgentController extends Controller
{
    public function makeFingerprint(): JsonResponse
    {
        $browserAgent = BrowserAgent::createBrowserAgent();

        if ($browserAgent) {
            return ResponseFactory::success('fingerprint_created', [
                'fingerprint' => $browserAgent->fingerprint,
            ], 201);
        }

        return ResponseFactory::error('fingerprint_not_created', null, 500);
    }

    public function validate(): JsonResponse
    {
        // This is a middleware, so if it reaches this point, the fingerprint is valid
        return ResponseFactory::success('valid_fingerprint');
    }
}
