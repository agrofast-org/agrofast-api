<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
  use HasFactory, Notifiable;

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
}
