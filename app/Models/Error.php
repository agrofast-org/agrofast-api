<?php

namespace App\Models;

class Error
{
    public readonly string $message;
    public readonly null|array|string $errors;

    public function __construct(string $message, null|array|string $errors = null)
    {
        $this->message = $message;
        $this->errors = $errors;
    }
}
