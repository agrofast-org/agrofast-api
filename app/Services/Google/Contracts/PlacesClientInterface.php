<?php

namespace App\Services\Google\Contracts;

interface PlacesClientInterface
{
    /**
     * Busca dados de um place pelo ID.
     *
     * @return array{formattedAddress: string, location: array{latitude: float, longitude: float}}
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function getPlaceData(string $placeId): array;
}
