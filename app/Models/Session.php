<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'hr.session';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'auth_code_id',
        'authenticated',
        'created_at',
        'last_activity',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_activity' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
