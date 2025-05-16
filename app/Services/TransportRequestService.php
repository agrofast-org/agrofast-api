<?php

namespace App\Services;

use App\Models\Transport\TransportRequest;
use Illuminate\Support\Facades\Http;

class TransportRequestService
{
    public function validateTransportRequest($requestId)
    {
        $req = TransportRequest::findOrFail($requestId);

        // --- 1) Detalhes do lugar de origem ---
        $originResp = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $req->origin_place_id,
            'key' => config('services.google.maps_key'),
        ])->throw()->json('result');

        $req->origin_place_name = $originResp['name'];
        $req->origin_latitude = $originResp['geometry']['location']['lat'];
        $req->origin_longitude = $originResp['geometry']['location']['lng'];

        // --- 2) Detalhes do lugar de destino ---
        $destResp = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $req->destination_place_id,
            'key' => config('services.google.maps_key'),
        ])->throw()->json('result');

        $req->destination_place_name = $destResp['name'];
        $req->destination_origin_latitude = $destResp['geometry']['location']['lat'];
        $req->destination_origin_longitude = $destResp['geometry']['location']['lng'];

        // --- 3) Matriz de distância/tempo ---
        $matrix = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => "{$req->origin_latitude},{$req->origin_longitude}",
            'destinations' => "{$req->destination_origin_latitude},{$req->destination_origin_longitude}",
            'key' => config('services.google.maps_key'),
        ])->throw()->json('rows.0.elements.0');

        if ($matrix['status'] === 'OK') {
            // distância em metros → converter para km, se preferir
            $req->distance = $matrix['distance']['value'] / 1000;
            // tempo estimado em texto (ex: “1 hora 20 min”)
            $req->estimated_time = $matrix['duration']['text'];
        }

        $req->save();
    }
}

