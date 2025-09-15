<?php

namespace App\Exception;

class InvalidFormException extends \Exception
{
    protected array $errors;

    public function __construct($errors, $message = 'Invalid form data', $code = 422, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
