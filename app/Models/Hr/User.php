<?php

namespace App\Models\Hr;

use App\Enums\UserError;
use App\Models\DynamicQuery;
use App\Models\LastError;
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
 * @property bool        $email_two_factor_auth
 * @property bool        $email_verified
 * @property null|Carbon $email_verified_at
 * @property bool        $number_two_factor_auth
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
    use LastError;

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
        'email_two_factor_auth',
        'email_verified',
        'email_verified_at',
        'number_two_factor_auth',
        'number_verified',
        'number_verified_at',
        'updated_at',
        'inactivated_at',
        'active',
        'profile_picture',
        'remember_token',
    ];

    protected $casts = [
        'email_two_factor_auth' => 'boolean',
        'email_authenticated' => 'boolean',
        'number_two_factor_auth' => 'boolean',
        'number_authenticated' => 'boolean',
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

    public static function getDecodedToken(): false|\stdClass
    {
        if (!empty(self::$decodedToken)) {
            return self::$decodedToken;
        }

        $token = request()->bearerToken();
        if (empty($token)) {
            self::setLastError(UserError::MISSING_TOKEN);

            return false;
        }

        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
        self::$decodedToken = $decoded;

        return $decoded;
    }

    /**
     * Authenticates the user based on the provided token.
     */
    public static function auth(): false|self
    {
        if (!empty(self::$user)) {
            return self::$user;
        }

        try {
            $decoded = self::getDecodedToken();

            if (gettype($decoded) === 'enum') {
                self::setLastError($decoded);

                return false;
            }

            if (!isset($decoded->sub)) {
                self::setLastError(UserError::INVALID_TOKEN);

                return false;
            }
            $user = self::where('id', $decoded->sub)->first();
            if (!$user) {
                self::setLastError(UserError::USER_NOT_FOUND);

                return false;
            }
            self::$user = $user;

            return $user;
        } catch (\Throwable) {
            self::setLastError(UserError::INVALID_TOKEN);

            return false;
        }
    }

    public static function session(): false|Session
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
            self::setLastError(UserError::SESSION_NOT_FOUND);

            return false;
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
