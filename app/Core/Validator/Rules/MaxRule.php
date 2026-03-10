<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;

class MaxRule implements RuleContract
{
    /**
     * The base maximum number.
     *
     * @var int
     */
    protected $max;

    /**
     * The string flag of the data.
     *
     * @var bool
     */
    protected $is_string;

    /**
     * The field or attribute of the data.
     *
     * @var string
     */
    protected $attribute;

    /** 
     * Default message template to be used if the user-defined value is not set or unavailable (String).
     * 
     * @var string
     */
    protected const DEFAULT_MESSAGE_TEMPLATE_STRING = 'The :attribute must not exceed :max characters';

    /** 
     * Default message template to be used if the user-defined value is not set or unavailable (Integer).
     * 
     * @var string
     */
    protected const DEFAULT_MESSAGE_TEMPLATE_INTEGER = 'The :attribute must not exceed :max';
    
    /**
     * Store the base maximum number.
     *
     * @param int $max
     */
    public function __construct($max) 
    {
        $this->max = $max;
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

        return $value <= $this->max;
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

        $max_type = $this->is_string 
            ? 'max_string' 
            : 'max_integer';

        $replacer = [
            ':attribute' => $this->attribute,
            ':max' => $this->max
        ];

        $template = config("constants.validator.error_message.$max_type", $default_template);

        return string_format($template, $replacer);
    }
}