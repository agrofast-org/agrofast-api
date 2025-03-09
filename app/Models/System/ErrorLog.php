<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $table = 'system.error_log';

    protected $fillable = [
        'url',
        'error_message',
        'stack_trace',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];
}
