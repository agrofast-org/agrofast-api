<?php

namespace App\Http\Controllers;

use App\Models\Transport\TransportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * List requests for the authenticated user.
     */
    public function listRequests(): JsonResponse
    {
        $user = Auth::user();
        $requests = TransportRequest::where('user_id', $user->id)->get();

        return response()->json(['data' => $requests], 200);
    }

    /**
     * Create a new transport request.
     */
    public function makeRequest(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        // Ensure user doesn't have an active request
        $existingRequest = TransportRequest::where('user_id', $user->id)
            ->where('active', true)
            ->first()
        ;

        if ($existingRequest) {
            return response()->json(['message' => 'Cannot make more than one request at a time'], 400);
        }

        try {
            TransportRequest::create([
                'user_id' => $user->id,
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
            ]);

            return response()->json(['message' => 'Request created'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to make request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing transport request.
     */
    public function updateRequest(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|exists:transport_requests,id',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
        ]);

        $transportRequest = TransportRequest::where('id', $validated['id'])
            ->where('user_id', $user->id)
            ->first()
        ;

        if (!$transportRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        try {
            $transportRequest->update([
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
            ]);

            return response()->json(['message' => 'Request updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an existing transport request.
     */
    public function cancelRequest(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|exists:transport_requests,id',
        ]);

        $transportRequest = TransportRequest::where('id', $validated['id'])
            ->where('user_id', $user->id)
            ->first()
        ;

        if (!$transportRequest) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        try {
            $transportRequest->update(['active' => false]);

            return response()->json(['message' => 'Request cancelled'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to cancel request', 'error' => $e->getMessage()], 500);
        }
    }
}
