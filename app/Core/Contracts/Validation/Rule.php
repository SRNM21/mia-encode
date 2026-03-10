<?php

namespace App\Core\Contracts\Validation;

interface Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param mixed  $value
     * @param string $attribute
     * @return bool
     */
    public function passes($value, $attribute) : bool;
    
    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message() : string;
}