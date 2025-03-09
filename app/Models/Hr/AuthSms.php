<?php

namespace App\Models\Hr;

use App\Jobs\SendSms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class AuthSms
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
class AuthSms extends Model
{
    use HasFactory;
    use Notifiable;

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
     *
     * @return AuthSms
     *
     * @throws \Exception
     */
    public static function createCode(int $userId): self
    {
        $user = User::find($userId);

        if (! $user) {
            throw new \Exception('User not found');
        }
        if (! self::validatePhoneNumber($user->number)) {
            throw new \Exception('Invalid phone number');
        }
        $code = (env('APP_ENV') === 'local' || env('ENVIRONMENT') === 'development') ? '111111' : rand(100000, 999999);
        self::where('user_id', $userId)->update(['active' => false]);
        $authCode = self::create([
            'user_id' => $userId,
            'code' => $code,
        ]);

        $smsEnabled = env('SMS_SERVICE_ENABLED', false);
        // Added this verification to avoid sending SMS in local environment. It's really expensive XD.
        if ($smsEnabled || $smsEnabled === 'true') {
            SendSms::dispatch($user->number, "Seu código de autenticação para o Agrofast é: {$code}");
        }

        return $authCode;
    }

    /**
     * Validate a phone number (simple example).
     *
     * @param string $number
     *
     * @return bool
     */
    private static function validatePhoneNumber(string $number): bool
    {
        return ! empty($number);
    }
}
