<?php

namespace App\Http\Controllers;

use App\Models\Hr\User;
use App\Models\Transport\Carrier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarrierController extends Controller
{
    /**
     * List carriers for the authenticated user.
     */
    public function listTransports(): JsonResponse
    {
        $user = User::auth();
        $carriers = Carrier::where('user_id', $user->id)->get();

        return response()->json(['data' => $carriers], 200);
    }

    /**
     * Create a new transport.
     */
    public function createTransport(Request $request): JsonResponse
    {
        $user = User::auth();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => 'required|string|max:255|unique:carriers,plate',
        ]);

        try {
            Carrier::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'model' => $validated['model'],
                'plate' => $validated['plate'],
            ]);

            return response()->json(['message' => 'Carrier created'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create carrier', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing transport.
     */
    public function updateTransport(Request $request): JsonResponse
    {
        $user = User::auth();

        $validated = $request->validate([
            'id' => 'required|exists:carriers,id',
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => 'required|string|max:255|unique:carriers,plate,'.$request->id,
        ]);

        $carrier = Carrier::where('id', $validated['id'])->where('user_id', $user->id)->first();

        if (!$carrier) {
            return response()->json(['message' => 'Carrier not found'], 404);
        }

        try {
            $carrier->update([
                'name' => $validated['name'],
                'model' => $validated['model'],
                'plate' => $validated['plate'],
            ]);

            return response()->json(['message' => 'Carrier updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update carrier', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Disable a transport.
     */
    public function disableTransport(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|exists:carriers,id',
        ]);

        $carrier = Carrier::where('id', $validated['id'])->where('user_id', $user->id)->first();

        if (!$carrier) {
            return response()->json(['message' => 'Carrier not found'], 404);
        }

        try {
            $carrier->update(['active' => false]);

            return response()->json(['message' => 'Carrier disabled'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to disable carrier', 'error' => $e->getMessage()], 500);
        }
    }
}
