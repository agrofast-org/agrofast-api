<?php

namespace App\Services\Google\Implementations;

use App\Services\Google\Contracts\DistanceMatrixClientInterface;
use Illuminate\Support\Facades\Http;

class HttpDistanceMatrixClient implements DistanceMatrixClientInterface
{
    public function getDistance(string $origin, string $destination): array
    {
        $response = Http::timeout(5)
            ->retry(2, 100)
            ->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => config('services.google.maps_key'),
            ])
        ;

        if ($response->failed()) {
            throw new \RuntimeException('Erro no Distance Matrix API');
        }

        $row = $response->json('rows.0.elements.0', []);
        if (empty($row) || ($row['status'] ?? '') !== 'OK') {
            throw new \InvalidArgumentException('Matriz inválida ou sem rota');
        }

        return [
            'distance' => $row['distance'],
            'duration' => $row['duration'],
        ];
    }
}
