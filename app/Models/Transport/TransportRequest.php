<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRequest extends Model
{
    use HasFactory;

    protected $table = 'transport.request';

    protected $fillable = [
        'user_id',
        'origin',
        'destination',
        'active',
    ];

    protected $attributes = [
        'active' => true,
    ];

    /**
     * Relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
