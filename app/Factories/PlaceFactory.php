<?php

namespace App\Factories;

use App\Models\Hr\User;
use App\Models\Transport\Place;
use Illuminate\Support\Str;

class PlaceFactory
{
    public static function create($data): false|Place
    {
        $uuid = Str::uuid()->toString();

        return Place::create([
            'uuid' => $uuid,
            'place_id' => $data['id'],
            'name' => $data['shortFormattedAddress'],
            'formatted_address' => $data['formattedAddress'],
            'color' => $data['iconBackgroundColor'] ?? '',
            'latitude' => $data['location']['latitude'],
            'longitude' => $data['location']['longitude'],
            'google_uri' => $data['googleMapsUri'] ?? '',
            'user_id' => User::auth()->id,
        ]);
    }
}
