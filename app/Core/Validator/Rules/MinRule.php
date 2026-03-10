<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;

class MinRule implements RuleContract
{
    /**
     * The base minimum number.
     *
     * @var int
     */
    protected $min;

    /**
     * The string flag of the data.
     *
     * @var bool
     */
    protected $is_string;

    /** 
     * Default message template to be used if the user-defined value is not set or unavailable (String).
     * 
     * @var string
     */
    protected const DEFAULT_MESSAGE_TEMPLATE_STRING = 'The :attribute must be at least :min characters';

    /** 
     * Default message template to be used if the user-defined value is not set or unavailable (Integer).
     * 
     * @var string
     */
    protected const DEFAULT_MESSAGE_TEMPLATE_INTEGER = 'The :attribute must be at least :min';
    
    /**
     * The field or attribute of the data.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Store the base minimum number.
     *
     * @param int $min
     */
    public function __construct($min) 
    {
        $this->min = $min;
    }

    /**
     * Check if the value passes the validation rule.
     *
     * @param string|int $value
     * @param string $attribute
     * @return boolean
     */
    public function passes($value, $attribute) : bool
    {
        $this->attribute = $attribute;

        if (!filter_var($value, FILTER_VALIDATE_INT))
        {
            $this->is_string = true;
            $value = strlen($value);
        }

        return $value >= $this->min;
    }

    /**
     * Returns an error message of the formatted template of validation rule.
     *
     * @return string
     */
    public function message() : string
    {
        $default_template = $this->is_string 
            ? self::DEFAULT_MESSAGE_TEMPLATE_STRING 
            : self::DEFAULT_MESSAGE_TEMPLATE_INTEGER;

        $min_type = $this->is_string 
            ? 'min_string' 
            : 'min_integer';

        $replacer = [
            ':attribute' => $this->attribute,
            ':min' => $this->min
        ];

        $template = config("constants.validator.error_message.$min_type", $default_template);

        return string_format($template, $replacer);
    }
}