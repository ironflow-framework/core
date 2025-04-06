<?php

namespace IronFlow\Http\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('The given data was invalid.');
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
