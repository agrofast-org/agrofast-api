<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'transport.offer';

    protected $fillable = [
        'user_id',
        'request_id',
        'carrier_id',
        'float',
        'active',
    ];

    protected $attributes = [
        'active' => true,
    ];

    /**
     * Relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with TransportRequest model.
     */
    public function request()
    {
        return $this->belongsTo(TransportRequest::class);
    }

    /**
     * Relationship with Carrier model.
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
