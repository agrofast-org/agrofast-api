<?php

namespace App\Services\Google\Implementations;

use App\Services\Google\Contracts\PlacesClientInterface;
use Illuminate\Support\Facades\Http;

class HttpPlacesClient implements PlacesClientInterface
{
    public function getPlaceData(string $placeId): array
    {
        $response = Http::withHeaders([
            'Referer' => env('WEB_URL'),
        ])
            ->timeout(5)
            ->retry(2, 100)
            ->get("https://places.googleapis.com/v1/places/{$placeId}", [
                'fields' => 'formatted_address,location',
                'key' => config('services.google.places_key'),
            ])
        ;

        if ($response->failed()) {
            throw new \RuntimeException("Erro ao buscar Place ID {$placeId}");
        }

        $data = $response->json();

        if (empty($data['formattedAddress']) || empty($data['location'])) {
            throw new \InvalidArgumentException("Dados incompletos para Place ID {$placeId}");
        }

        return [
            'formattedAddress' => $data['formattedAddress'],
            'location' => [
                'latitude' => $data['location']['latitude'],
                'longitude' => $data['location']['longitude'],
            ],
        ];
    }
}
