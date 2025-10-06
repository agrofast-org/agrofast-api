<?php

namespace App\Exception;

class InvalidRequestException extends \Exception
{
    protected array $data;

    public function __construct(array $data, string $message = 'Invalid request', $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }
}
