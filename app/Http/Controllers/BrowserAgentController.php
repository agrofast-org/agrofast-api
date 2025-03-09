<?php

namespace App\Http\Controllers;

use App\Models\Hr\BrowserAgent;
use Illuminate\Http\JsonResponse;

class BrowserAgentController extends Controller
{
    public function makeFingerprint(): JsonResponse
    {
        $browserAgent = BrowserAgent::createBrowserAgent();

        if ($browserAgent) {
            return response()->json([
                'message' => 'fingerprint_created',
                'fingerprint' => $browserAgent->fingerprint,
            ], 201);
        }

        return response()->json([
            'message' => 'fingerprint_not_created',
        ], 500);
    }

    public function validate(): JsonResponse
    {
        // This is a middleware, so if it reaches this point, the fingerprint is valid
        return response()->json([
            'message' => 'valid_fingerprint',
        ], 200);
    }
}
