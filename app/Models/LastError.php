<?php

namespace App\Models;

trait LastError
{
    protected static ?string $lastError = null;

    public static function setLastError(mixed $error): void
    {
        self::$lastError = (string) $error;
    }

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }
}
