<?php

namespace App\Models;

class Success
{
    public readonly string $message;
    public readonly null|array|string $data;

    public function __construct(string $message, null|array|string $data = null)
    {
        $this->message = $message;
        $this->data = $data;
    }
}
