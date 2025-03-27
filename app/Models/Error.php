<?php

namespace App\Models;

use App\Models\System\ErrorLog;

class Error
{
    public readonly string $message;
    public readonly null|array|string $errors;
    public readonly ?array $data;
    public readonly int $code;

    public function __construct(string $message, null|array|string $errors = null, ?array $data = null, int $code = 400)
    {
        $this->message = $message;
        $this->errors = $errors;
        $this->data = $data;
        $this->code = $code;

        ErrorLog::create([
            'url' => request()->url(),
            'error_message' => $message,
            'stack_trace' => '',
        ]);
    }
}
