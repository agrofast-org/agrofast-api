<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRequest extends Model
{
    use HasFactory;
    protected $table    = 'transport_request';
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
