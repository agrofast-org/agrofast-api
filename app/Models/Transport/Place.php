<?php

namespace App\Models\Transport;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Place.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $place_id
 * @property null|string $name
 * @property null|string $formatted_address
 * @property null|string $color
 * @property null|string $latitude
 * @property null|string $longitude
 * @property null|string $google_uri
 * @property int         $user_id
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class Place extends Model
{
    protected $table = 'transport.place';

    protected $fillable = [
        'uuid',
        'place_id',
        'name',
        'formatted_address',
        'color',
        'latitude',
        'longitude',
        'google_uri',
        'user_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];
}
