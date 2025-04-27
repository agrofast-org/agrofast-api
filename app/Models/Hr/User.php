<?php

namespace App\Models\Hr;

use App\Enums\UserError;
use App\Models\DynamicQuery;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Class User.
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $name
 * @property string      $surname
 * @property string      $email
 * @property string      $number
 * @property string      $password
 * @property string      $language
 * @property null|string $profile_type
 * @property bool        $email_verified
 * @property null|Carbon $email_verified_at
 * @property bool        $number_verified
 * @property null|Carbon $number_verified_at
 * @property bool        $active
 * @property null|string $profile_picture
 * @property null|string $remember_token
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class User extends DynamicQuery
{
    use HasFactory;
    use Notifiable;

    protected static User $user;

    protected static Session $session;

    protected static \stdClass $decodedToken;

    protected $table = 'hr.user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'name',
        'surname',
        'number',
        'email',
        'password',
        'profile_type',
        'language',
        'email_verified',
        'email_verified_at',
        'number_verified',
        'number_verified_at',
        'updated_at',
        'inactivated_at',
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
        'inactivated_at',
        'email_verified_at',
        'number_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function getDecodedToken(): \stdClass|UserError
    {
        if (!empty(self::$decodedToken)) {
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
     */
    public static function auth(): self|UserError
    {
        if (!empty(self::$user)) {
            return self::$user;
        }

        try {
            $decoded = self::getDecodedToken();

            if (gettype($decoded) === 'enum') {
                return $decoded;
            }

            if (!isset($decoded->sub)) {
                return UserError::INVALID_TOKEN;
            }
            $user = self::where('id', $decoded->sub)->first();
            if (!$user) {
                return UserError::USER_NOT_FOUND;
            }
            self::$user = $user;

            return $user;
        } catch (\Throwable) {
            return UserError::INVALID_TOKEN;
        }
    }

    public static function session(): Session|UserError
    {
        if (!empty(self::$session)) {
            return self::$session;
        }
        $decoded = self::getDecodedToken();

        if (gettype($decoded) === 'enum') {
            return $decoded;
        }

        $session = Session::where('id', $decoded->sid)->first();
        if (!$session) {
            return UserError::SESSION_NOT_FOUND;
        }
        self::$session = $session;

        return $session;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id', 'id');
    }

    public function payment_methods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id', 'id');
    }
}
