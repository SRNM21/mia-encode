<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;

class InRule implements RuleContract
{
    /**
     * The available values to be selected.
     *
     * @var array
     */
    protected $items;

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
    protected const DEFAULT_MESSAGE_TEMPLATE = 'The selected :attribute is not in the list of allowed values';

    /**
     * Store all available choices.
     *
     * @param string $items
     */
    public function __construct($items) 
    {
        $this->items = explode(',', $items);
    }

    /**
     * Check if the value passes the validation rule.
     *
     * @param string $value
     * @param string $attribute
     * @return boolean
     */
    public function passes($value, $attribute) : bool
    {
        $this->attribute = $attribute;
        return in_array($value, $this->items);
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

        $template = config('constants.validator.error_message.required', self::DEFAULT_MESSAGE_TEMPLATE);

        return string_format($template, $replacer);
    }
}