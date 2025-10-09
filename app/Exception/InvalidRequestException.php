<?php

namespace App\Exception;

class InvalidRequestException extends \Exception
{
    protected array $data;

    public function __construct(string $message = 'Invalid request', array $data, $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }
}
