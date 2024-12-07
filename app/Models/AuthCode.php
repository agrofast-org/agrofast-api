<?php

namespace App\Models;

use App\Services\SmsSender;
use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    protected $table    = 'auth_code';
    protected $fillable = [
      'user_id',
      'code',
      'attempts',
      'active',
    ];

    protected $attributes = [
      'attempts' => 0,
      'active'   => true,
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
    public static function createCode(int $userId): AuthCode
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User not found');
        }
        if (!self::validatePhoneNumber($user->number)) {
            throw new \Exception('Invalid phone number');
        }
        $code = env('APP_ENV') === 'local' ? '1111' : rand(1000, 9999);
        self::where('user_id', $userId)->update(['active' => false]);
        $authCode = self::create([
          'user_id' => $userId,
          'code'    => $code,
        ]);

        SmsSender::send($user->number, "Seu código de autenticação para o Agrofast é: {$code}");

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
        return !empty($number);
    }
}
