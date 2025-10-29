<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Summary of UserMercadoPago model representing a user's Mercado Pago account details.
 *
 * #file:database/migrations/2025_10_29_083712_create_user_mercado_pago_table.php
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $full_name
 * @property string      $cpf
 * @property null|string $email
 * @property null|string $phone
 * @property null|string $mp_user_id
 * @property null|string $mp_access_token
 * @property null|string $mp_refresh_token
 * @property null|Carbon $mp_token_expires_at
 * @property string      $status
 * @property null|Carbon $created_at
 * @property null|Carbon $updated_at
 * @property null|Carbon $deleted_at
 */
class UserMercadoPago extends Model
{
    use SoftDeletes;

    protected $table = 'hr.user_mercado_pago';

    protected $fillable = [
        'user_id',
        'full_name',
        'cpf',
        'email',
        'phone',
        'mp_user_id',
        'mp_access_token',
        'mp_refresh_token',
        'mp_token_expires_at',
        'status',
    ];

    protected $casts = [
        'mp_token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected' && !empty($this->mp_access_token);
    }
}
