<?php

namespace App\Models;

use App\Enums\UserError;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use stdClass;

/**
 * Class User
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int $id
 * @property int $uuid
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
class User extends DynamicQuery
{
    use HasFactory, Notifiable;

    protected static User $user;

    protected static stdClass $decodedToken;

    protected $table = 'hr.user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
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

    public static function getDecodedToken(): stdClass | UserError
    {
        if (self::$decodedToken) {
            return self::$decodedToken;
        }

        $token = request()->bearerToken();
        if (empty($token)) {
            return UserError::MISSING_TOKEN;
        }

        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
        self::$decodedToken = $decoded;

        return $decoded;
    }

    /**
     * Authenticates the user based on the provided token.
     *
     * @return User|UserError
     */
    public static function auth(): self | UserError
    {
        if (self::$user) {
            return self::$user;
        }

        try {
            $decoded = self::getDecodedToken();

            if (typeof($decoded) === 'enum') {
                return $decoded;
            }

            if (! isset($decoded->sub)) {
                return UserError::INVALID_TOKEN;
            }
            $user = self::where('id', $decoded->sub)->first();
            if (! $user) {
                return UserError::USER_NOT_FOUND;
            }
            self::$user = $user;

            return $user;
        } catch (\Throwable) {
            return UserError::INVALID_TOKEN;
        }
    }

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
