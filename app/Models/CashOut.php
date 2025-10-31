<?php

namespace App\Models;

use App\Enums\CashOutStatus;
use Illuminate\Database\Eloquent\Model;

class CashOut extends Model
{
    protected $casts = [
        'status' => CashOutStatus::class,
    ];
}
