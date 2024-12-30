<?php

namespace App\Models;

use App\Services\SmsSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AuthCode extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'hr.auth_code';

    protected $fillable = [
        'user_id',
        'code',
        'attempts',
        'active',
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
     * @return AuthCode
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
        $code = (env('APP_ENV') === 'local' || env('ENVIRONMENT') === 'development') ? '1111' : rand(1000, 9999);
        self::where('user_id', $userId)->update(['active' => false]);
        $authCode = self::create([
            'user_id' => $userId,
            'code' => $code,
        ]);

        $smsEnabled = env('SMS_SERVICE_ENABLED', false);
        // Added this verification to avoid sending SMS in local environment. It's really expensive XD.
        if ($smsEnabled || $smsEnabled === 'true') {
            SmsSender::send($user->number, "Seu código de autenticação para o Agrofast é: {$code}");
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
