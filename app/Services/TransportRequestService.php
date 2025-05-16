<?php

namespace App\Services;

use App\Models\Transport\TransportRequest;
use Illuminate\Support\Facades\Http;

class TransportRequestService
{
    public function validateTransportRequest($requestId)
    {
        $request = TransportRequest::findOrFail($requestId);

        $originData = $this->getPlace($request->origin_place_id, 'formatted_address,location');
        if (empty($originData['formattedAddress']) || empty($originData['location'])) {
            $request->state = 'rejected';
            $request->save();
            return;
        }
        $request->origin_place_name = $originData['formattedAddress'];
        $request->origin_latitude = $originData['location']['latitude'];
        $request->origin_longitude = $originData['location']['longitude'];

        $destinationResp = $this->getPlace($request->destination_place_id, 'formatted_address,location');
        if (empty($destinationResp['formattedAddress']) || empty($destinationResp['location'])) {
            $request->state = 'rejected';
            $request->save();
            return;
        }

        $request->origin_place_name = $destinationResp['formattedAddress'];
        $request->origin_latitude = $destinationResp['location']['latitude'];
        $request->origin_longitude = $destinationResp['location']['longitude'];

        $matrix = $this->getDistanceMatrix(
            "{$request->origin_latitude},{$request->origin_longitude}",
            "{$request->destination_origin_latitude},{$request->destination_origin_longitude}"
        );

        if ($matrix['status'] === 'OK') {
            $request->distance = $matrix['distance']['value'] / 1000;
            $request->estimated_time = $matrix['duration']['text'];

            $request->state = 'approved';
        }

        $request->save();
    }

    protected function makeRequest()
    {
        return Http::withHeaders([
            'Referer' => env('APP_URL'),
        ]);
    }

    protected function getPlace(string $placeId, string $fields = '*')
    {
        return $this->makeRequest()->get("https://places.googleapis.com/v1/places/{$placeId}", [
            'fields' => $fields,
            'key' => config('services.google.maps_key'),
        ])->throw()->json();
    }

    protected function getDistanceMatrix(string $origin, string $destination)
    {
        return $this->makeRequest()->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $origin,
            'destinations' => $destination,
            'units' => 'imperial',
            'key' => config('services.google.maps_key'),
        ])->throw()->json();
    }
}
