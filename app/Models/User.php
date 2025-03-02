<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * Class User
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $number
 * @property string $email
 * @property string $password
 * @property bool $number_verified
 * @property \Carbon\Carbon|null $number_verified_at
 * @property bool $email_verified
 * @property \Carbon\Carbon|null $email_verified_at
 * @property bool $active
 * @property string|null $profile_picture
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'active'               => 'boolean',
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
     * Mutator for password hashing.
     *
     * @param string $value Plain text password.
     * @return void
     */
    public function setPasswordAttribute($value): void
    {
        if (Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Prepares data for insertion by normalizing and sanitizing inputs.
     *
     * @param array $params Data received from the request.
     * @return array Prepared data for insertion.
     */
    public static function prepareInsert(array $params): array
    {
        if (isset($params['email'])) {
            $params['email'] = strtolower($params['email']);
        }
        if (isset($params['number'])) {
            $params['number'] = preg_replace('/\D/', '', $params['number']);
        }

        return $params;
    }
}
