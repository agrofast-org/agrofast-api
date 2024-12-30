<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'hr.user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'surname',
        'number',
        'email',
        'password',
        'number_verified',
        'number_verified_at',
        'email_verified',
        'email_verified_at',
        'active',
        'profile_picture',
        'remember_token',
    ];

    protected $casts = [
        'number_authenticated' => 'boolean',
        'email_authenticated' => 'boolean',
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mutator to hash the password.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public static function prepareInsert(array $params): array
    {
        $params['number'] = preg_replace('/\D/', '', $params['number']);

        return $params;
    }

    public static function validateInsert(array $params): array
    {
        $arErr = [];

        $arErr = array_merge($arErr, self::validateRequiredField($params, 'name', 'user_name_required_message'));
        $arErr = array_merge($arErr, self::validateRequiredField($params, 'surname', 'user_surname_required_message'));
        $arErr = array_merge($arErr, self::validatePhoneNumber($params['number'] ?? null));
        $arErr = array_merge($arErr, self::validatePassword($params['password'] ?? null, 'password'));

        if (empty($arErr['password']) && empty($arErr['password_confirm']) && $params['password'] !== $params['password_confirm']) {
            $arErr['password_confirm'][] = 'password_not_coincide_message';
            $arErr['password'][] = 'password_not_coincide_message';
        }
        if (! empty($arErr['password'])) {
            $arErr['password_confirm'] = $arErr['password'];
        }

        return $arErr;
    }

    public static function validateUpdate(array $params): array
    {
        return self::validateInsert($params);
    }

    private static function validateRequiredField(array $params, string $field, string $message): array
    {
        $arErr = [];
        if (! isset($params[$field]) || empty($params[$field])) {
            $arErr[$field] = $message;
        }

        return $arErr;
    }

    private static function validatePhoneNumber(string|null $number = null): array
    {
        $arErr = [];
        if (empty($number)) {
            $arErr['number'][] = 'user_number_required_message';

            return $arErr;
        }
        $cleanedNumber = preg_replace('/\D/', '', $number);
        $length = strlen($cleanedNumber);
        if ($length !== 13) {
            $arErr['number'][] = 'user_invalid_number_message';

            return $arErr;
        }

        return $arErr;
    }

    private static function validateEmail(string|null $email = null): array
    {
        $arErr = [];
        if (empty($email)) {
            $arErr['email'][] = 'user_email_required_message';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $arErr['email'][] = 'user_invalid_email_message';
        }

        return $arErr;
    }

    private static function validatePassword(string|null $password = null, string $field = 'password'): array
    {
        $arErr = [];
        if (! isset($password) || empty($password)) {
            $arErr[$field][] = 'user_password_required_message';
        } elseif (strlen($password) < 8) {
            $arErr[$field][] = 'user_password_length_message';
        } elseif (! preg_match('/[A-Za-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
            $arErr[$field][] = 'user_password_character_message';
        }

        return $arErr;
    }
}
