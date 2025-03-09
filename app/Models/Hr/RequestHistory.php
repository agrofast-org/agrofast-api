<?php

namespace App\Models\Hr;

use App\Models\DynamicQuery;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RequestHistory
 *
 * Represents a request history record with associated attributes and relationships.
 *
 * @property int $id
 * @property int $session_id
 * @property string $route
 * @property string $method
 * @property string|null $payload
 * @property \Carbon\Carbon $created_at
 * @property-read Session $session
 */
class RequestHistory extends DynamicQuery
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.request_history';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'route',
        'method',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the session that owns the request history.
     *
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    /**
     * Log a new request history entry.
     *
     * @param int $sessionId
     * @param string $route
     * @param string $method
     * @param string|null $payload
     * @return self
     */
    public static function logRequest(int $sessionId, string $route, string $method, ?string $payload = null): self
    {
        return self::create([
            'session_id' => $sessionId,
            'route' => $route,
            'method' => $method,
            'payload' => $payload,
        ]);
    }
}
