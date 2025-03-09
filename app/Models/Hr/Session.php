<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Session
 *
 * Represents a user session with associated information about device, authentication status and activity.
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip_address
 * @property int $browser_agent_id
 * @property string $auth_type
 * @property int|null $auth_code_id
 * @property bool $authenticated
 * @property bool $active
 * @property \Carbon\Carbon|null $last_activity
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $inactivated_at
 * @property-read User $user
 * @property-read BrowserAgent $browserAgent
 * @property-read AuthCode|null $authCode
 * @property-read \Illuminate\Database\Eloquent\Collection|RequestHistory[] $requestHistory
 */
class Session extends Model
{
    use HasFactory;

    protected $table = 'hr.session';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'ip_address',
        'browser_agent_id',
        'auth_type',
        'auth_code_id',
        'authenticated',
        'active',
        'last_activity',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    protected $casts = [
        'authenticated' => 'boolean',
        'active' => 'boolean',
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'inactivated_at' => 'datetime',
    ];

    protected $dates = [
        'last_activity',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the browser agent associated with this session.
     */
    public function browserAgent()
    {
        return $this->belongsTo(BrowserAgent::class, 'browser_agent_id', 'id');
    }

    /**
     * Get the auth code associated with this session.
     */
    public function authCode()
    {
        return $this->belongsTo(AuthCode::class, 'auth_code_id', 'id');
    }

    /**
     * Get the request history records for this session.
     */
    public function requestHistory()
    {
        return $this->hasMany(RequestHistory::class, 'session_id', 'id');
    }
}
