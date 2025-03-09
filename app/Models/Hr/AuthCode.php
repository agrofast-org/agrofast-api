<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class AuthCode
 *
 * Represents an authentication code with associated attributes and logic.
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $auth_type
 * @property bool $authenticated
 * @property string $code
 * @property int $attempts
 * @property bool $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $inactivated_at
 */
class AuthCode extends Model
{
    use HasFactory;
    use Notifiable;

    public const SMS = 'sms';

    public const EMAIL = 'email';

    protected $table = 'hr.auth_code';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'auth_type',
        'authenticated',
        'code',
        'attempts',
        'active',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    protected $attributes = [
        'attempts' => 0,
        'active' => true,
    ];

    /**
     * Generate a new authentication code for the user.
     *
     * @param int $userId
     * @param self::SMS | self::EMAIL $userId
     *
     * @return AuthEmail | AuthSms
     *
     * @throws \Exception
     */
    public static function createCode(int $userId, string $authType): AuthEmail | AuthSms
    {
        $authCode = null;

        if ($authType === self::SMS) {
            $authCode = AuthSms::createCode($userId);
        } elseif ($authType === self::EMAIL) {
            $authCode = AuthEmail::createCode($userId);
        }

        return $authCode;
    }
}
