<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;

class IntegerRule implements RuleContract
{
    /**
     * The field or attribute of the data.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Default message template to be used if the user-defined value is not set or unavailable.
     * 
     * @var string
     */
    protected const DEFAULT_MESSAGE_TEMPLATE = 'The :attribue must be an integer';

    /**
     * Check if the value passes the validation rule.
     *
     * @param mixed $value
     * @param string $attribute
     * @return boolean
     */
    public function passes($value, $attribute) : bool
    {
        $this->attribute = $attribute;

        return filter_var($value, FILTER_VALIDATE_INT);
    }
    
    /**
     * Returns an error message of the formatted template of validation rule.
     *
     * @return string
     */
    public function message() : string
    {
        $replacer = [
            ':attribute' => $this->attribute
        ];
        
        $template = config('constants.validator.error_message.integer', self::DEFAULT_MESSAGE_TEMPLATE);

        return string_format($template, $replacer);
    }
}