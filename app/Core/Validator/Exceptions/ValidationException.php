<?php

namespace App\Core\Validator\Exceptions;

class ValidationException extends \Exception 
{
    /**
     * Error messages from the initialization.
     *
     * @var array|string
     */
    protected $errors;

    /**
     * Create a new exception instance.
     *
     * @param array|string $errors
     * @param string $message
     * @param integer $code
     * @param Exception|null $previous
     */
    public function __construct($errors, $message = 'Validation failed', $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Returns the error message bag.
     *
     * @return array|string
     */
    public function getErrors()
    {
        return $this->errors;
    }
}