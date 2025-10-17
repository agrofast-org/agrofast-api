<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * #FILE: TransportRequest.php.
 *
 * Represents a transport service request with details about the origin, destination,
 * estimated travel time, desired date, and request state.
 *
 * @property int         $id                           Unique identifier of the transport request.
 * @property int         $user_id                      Identifier of the user who requested the transport.
 * @property int         $machine_id                   Identifier of the machine used for the transport.
 * @property int         $origin_place_id              Identifier for the origin location.
 * @property string      $origin_place_name            Name of the origin place.
 * @property float       $origin_latitude              Latitude coordinate of the origin.
 * @property float       $origin_longitude             Longitude coordinate of the origin.
 * @property int         $destination_place_id         Identifier for the destination location.
 * @property string      $destination_place_name       Name of the destination.
 * @property float       $destination_origin_latitude  Latitude coordinate of the destination.
 * @property float       $destination_origin_longitude Longitude coordinate of the destination.
 * @property float       $distance                     Distance between origin and destination.
 * @property string      $estimated_time               Estimated travel time.
 * @property string      $estimated_cost               Estimated cost of the transport.
 * @property int         $payment_id                   Identifier for the associated Pix payment.
 * @property null|string $payment_pix_url              URL for the Pix payment.
 * @property null|string $payment_pix_qr_code          QR code for the Pix payment.
 * @property Carbon      $desired_date                 Desired date and time for the transport.
 * @property string      $state                        Current state of the request (e.g., 'pending').
 * @property bool        $active                       Indicates if the request is active.
 * @property null|Carbon $inactivated_at               Timestamp when the request was deactivated, if applicable.
 * @property Carbon      $created_at                   Timestamp when the request was created.
 * @property Carbon      $updated_at                   Timestamp when the request was last updated.
 * @property User        $user                         The user who owns the transport request.
 */
class Request extends Model
{
    use HasFactory;

    public const STATE_PENDING = 'pending';
    public const STATE_WAITING_FOR_OFFER = 'waiting_for_offer';
    public const STATE_PAYMENT_PENDING = 'payment_pending';
    public const STATE_APPROVED = 'approved';
    public const STATE_REJECTED = 'rejected';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_CANCELED = 'canceled';
    public const STATE_COMPLETED = 'completed';

    protected $table = 'transport.request';

    protected $fillable = [
        'uuid',
        'user_id',
        'machine_id',

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
        'estimated_cost',

        'payment_id',

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
        'desired_date' => 'datetime',
        'inactivated_at' => 'datetime',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getEstimatedCost($distance)
    {
        // Assuming cost is $2.5 per km
        return number_format($distance / 1000 * 2.5, 2);
    }

    /**
     * Relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
