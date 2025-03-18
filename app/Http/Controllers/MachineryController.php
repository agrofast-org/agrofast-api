<?php

namespace App\Http\Controllers;

use App\Models\Transport\Machinery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineryController extends Controller
{
    /**
     * List all machinery for the authenticated user.
     */
    public function listMachinery(): JsonResponse
    {
        $user = Auth::user();

        $machines = Machinery::where('user_id', $user->id)->get();

        return response()->json([
            'data' => $machines,
        ], 200);
    }

    /**
     * Create a new machine for the authenticated user.
     */
    public function createMachine(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => 'required|string|max:255|unique:machineries,plate',
        ]);

        try {
            $machinery = Machinery::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'model' => $validated['model'],
                'plate' => $validated['plate'],
            ]);

            return response()->json([
                'message' => 'Machine created successfully',
                'data' => $machinery,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create machine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing machine for the authenticated user.
     */
    public function updateMachine(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|integer|exists:machineries,id',
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'plate' => 'required|string|max:255|unique:machineries,plate,'.$request->id,
        ]);

        $machinery = Machinery::where('id', $validated['id'])
            ->where('user_id', $user->id)
            ->first()
        ;

        if (!$machinery) {
            return response()->json([
                'message' => 'Machine not found',
            ], 404);
        }

        try {
            $machinery->update([
                'name' => $validated['name'],
                'model' => $validated['model'],
                'plate' => $validated['plate'],
            ]);

            return response()->json([
                'message' => 'Machine updated successfully',
                'data' => $machinery,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update machine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disable a machine for the authenticated user.
     */
    public function disableMachine(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|integer|exists:machineries,id',
        ]);

        $machinery = Machinery::where('id', $validated['id'])
            ->where('user_id', $user->id)
            ->first()
        ;

        if (!$machinery) {
            return response()->json([
                'message' => 'Machine not found',
            ], 404);
        }

        try {
            $machinery->update(['active' => false]);

            return response()->json([
                'message' => 'Machine disabled successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to disable machine',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
