<?php

namespace App\Http\Controllers;

use App\Models\Transport\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    /**
     * List all offers for the authenticated user.
     */
    public function listOffers(): JsonResponse
    {
        $user = Auth::user();
        $offers = Offer::where('user_id', $user->id)->get();

        return response()->json(['data' => $offers], 200);
    }

    /**
     * Create a new offer.
     */
    public function makeOffer(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'request_id' => 'required|exists:transport_requests,id',
            'carrier_id' => 'required|exists:carriers,id',
            'float' => 'required|numeric|min:0',
        ]);

        // Ensure user doesn't have an active offer
        $existingOffer = Offer::where('user_id', $user->id)
            ->where('active', true)
            ->first()
        ;

        if ($existingOffer) {
            return response()->json(['message' => 'Cannot make more than one offer at a time'], 400);
        }

        try {
            Offer::create([
                'user_id' => $user->id,
                'request_id' => $validated['request_id'],
                'carrier_id' => $validated['carrier_id'],
                'float' => $validated['float'],
            ]);

            return response()->json(['message' => 'Offer created'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create offer', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing offer.
     */
    public function updateOffer(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|exists:offers,id',
            'request_id' => 'required|exists:transport_requests,id',
            'carrier_id' => 'required|exists:carriers,id',
            'float' => 'required|numeric|min:0',
        ]);

        $offer = Offer::where('id', $validated['id'])->where('user_id', $user->id)->first();

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        try {
            $offer->update([
                'request_id' => $validated['request_id'],
                'carrier_id' => $validated['carrier_id'],
                'float' => $validated['float'],
            ]);

            return response()->json(['message' => 'Offer updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update offer', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an offer.
     */
    public function cancelOffer(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|exists:offers,id',
        ]);

        $offer = Offer::where('id', $validated['id'])->where('user_id', $user->id)->first();

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        try {
            $offer->update(['active' => false]);

            return response()->json(['message' => 'Offer cancelled'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to cancel offer', 'error' => $e->getMessage()], 500);
        }
    }
}
