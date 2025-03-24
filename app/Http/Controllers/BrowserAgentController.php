<?php

namespace App\Http\Controllers;

use App\Factories\BrowserAgentFactory;
use App\Factories\ResponseFactory;
use App\Models\Hr\BrowserAgent;
use Illuminate\Http\JsonResponse;

class BrowserAgentController extends Controller
{
    public function makeFingerprint(): JsonResponse
    {
        $fingerprint = request()->header('Browser-Agent');
        if ($fingerprint) {
            $browserAgent = BrowserAgent::validateFingerprint($fingerprint);
            if ($browserAgent) {
                return ResponseFactory::success('valid_fingerprint');
            }
        }

        $browserAgent = BrowserAgentFactory::create();

        if ($browserAgent) {
            return ResponseFactory::success('new_fingerprint', [
                'fingerprint' => $browserAgent->fingerprint,
            ], 201);
        }

        return ResponseFactory::error('fingerprint_not_created', null, null, 500);
    }

    public function validate(): JsonResponse
    {
        // This is a middleware, so if it reaches this point, the fingerprint is valid
        return ResponseFactory::success('valid_fingerprint');
    }
}
