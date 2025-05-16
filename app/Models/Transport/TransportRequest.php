<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRequest extends Model
{
    use HasFactory;

    protected $table = 'transport.request';

    protected $fillable = [
        'user_id',

        'origin_place_id',
        'origin_place_name',
        'origin_latitude',
        'origin_longitude',

        'destination_place_id',
        'destination_place_name',
        'destination_origin_latitude',
        'destination_origin_longitude',

        'distance',
        'estimated_time',
        'desired_date',
        'state',
        'active',
        'inactivated_at',
    ];

    protected $attributes = [
        'active' => true,
        'state' => 'pending',
    ];

    protected $casts = [
        'desired_date'   => 'datetime',
        'inactivated_at' => 'datetime',
        'active'         => 'boolean',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * Relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
