<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    protected $table = 'hr.user';
    protected $fillable = [
        'name',
        'surname',
        'profile_picture',
        'number',
        'email',
        'authenticated',
        'active',
    ];

    protected $casts = [
        'authenticated' => 'boolean',
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
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

    public static function validateInsert(array $params): array
    {
        $arErr = [];
        if (!isset($params["name"]) || empty($params["name"])) {
            $arErr["name"] = "user_name_required_message";
        }
        if (!isset($params["surname"]) || empty($params["surname"])) {
            $arErr["surname"] = "user_surname_required_message";
        }
        $numberErr = self::validatePhoneNumber($params["number"]);
        if (!empty($numberErr)) {
            $arErr["number"] = $numberErr;
        }
        $passwordErr = self::validatePassword($params["password"]);
        if (!empty($passwordErr)) {
            $arErr["password"] = $passwordErr;
        }
        if ($params["password"] !== $params["password_confirm"]) {
            $arErr["password_confirm"] = "password_not_coincide_message";
            $arErr["password"][] = "password_not_coincide_message";
        }
        if (empty($params["password_confirm"])) {
            $arErr["password_confirm"] = "password_confirm_required_message";
        }
        return $arErr;
    }

    public static function validateUpdate(array $params): array
    {
        return self::validateInsert($params);
    }

    private static function validatePhoneNumber(string|null $number = null): array
    {
        $arErr = [];
        if (empty($number)) {
            $arErr[] = 'user_number_required_message';
        }
        if (!preg_match('/^\+?[0-9]+$/', $number) || strlen(preg_replace('/\D/', '', $number)) < 10 || strlen(preg_replace('/\D/', '', $number)) > 14) {
            $arErr[] = 'user_invalid_number_message';
        }
        return $arErr;
    }

    private static function validateEmail(string|null $email = null): array
    {
        $arErr = [];
        if (empty($email)) {
            $arErr[] = 'user_email_required_message';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $arErr[] = 'user_invalid_email_message';
        }
        return $arErr;
    }
}
