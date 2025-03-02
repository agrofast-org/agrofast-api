<?php

namespace App\Models;

use App\Jobs\SendMail;
use App\Mail\AuthenticationMail;
use App\Mail\FirstLoginMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AuthEmail extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'hr.auth_email';

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
     * @return AuthEmail
     *
     * @throws \Exception
     */
    public static function createCode(int $userId): self
    {
        $user = User::find($userId);

        if (! $user) {
            throw new \Exception('User not found');
        }
        if (! self::validateEmail($user->email)) {
            throw new \Exception('Invalid Email');
        }
        $code = (env('APP_ENV') === 'local' || env('ENVIRONMENT') === 'development') ? '1111' : rand(1000, 9999);
        self::where('user_id', $userId)->update(['active' => false]);
        $authCode = self::create([
            'user_id' => $userId,
            'code' => $code,
        ]);

        $mailData = [
            'user' => $user,
            'info' => [
                'code' => $code,
                'expires' => now()->addMinutes(10),
            ],
        ];

        if (! $user->email_verified) {
            SendMail::dispatch($user->email, FirstLoginMail::class, $mailData);
        } else {
            SendMail::dispatch($user->email, AuthenticationMail::class, $mailData);
        }

        return $authCode;
    }

    /**
     * Validate a phone Email (simple example).
     *
     * @param string $Email
     *
     * @return bool
     */
    private static function validateEmail(string $email): bool
    {
        return ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
