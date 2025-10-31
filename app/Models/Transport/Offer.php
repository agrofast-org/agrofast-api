<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use App\Support\Traits\HasProgressState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Offer model representing a transport offer stored in transport.offers.
 *
 * #file:2025_03_03_175131_create_offers_table.php
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property int         $request_id
 * @property int         $carrier_id
 * @property string      $price          Decimal (10,2) stored as string
 * @property string      $gain           Decimal (10,2) stored as string
 * @property string      $state
 * @property int         $rate
 * @property string|null $comment
 * @property bool        $active
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 * @property null|Carbon $inactivated_at
 */
class Offer extends Model
{
    use HasFactory;
    use HasProgressState;

    protected $table = 'transport.offer';

    protected $fillable = [
        'uuid',
        'user_id',
        'request_id',
        'carrier_id',
        'price',
        'gain',
        'state',
        'rate',
        'comment',
        'active',
    ];

    protected $attributes = [
        'active' => true,
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
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
        return $this->belongsTo(Request::class);
    }

    /**
     * Relationship with Carrier model.
     */
    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
