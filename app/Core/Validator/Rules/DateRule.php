<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;
use DateTime;

class DateRule implements RuleContract
{
    /**
     * Optional date format.
     *
     * @var string|null
     */
    protected $format;

    /**
     * The field or attribute of the data.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Default message template.
     */
    protected const DEFAULT_MESSAGE_TEMPLATE = 'The :attribute must be a valid date';

    /**
     * Create rule instance.
     *
     * Example:
     *  date:Y-m-d
     */
    public function __construct($format = null)
    {
        $this->format = $format;
    }

    /**
     * Check if the value passes the validation rule.
     */
    public function passes($value, $attribute): bool
    {
        $this->attribute = $attribute;

        if (!$value) {
            return false;
        }

        if ($this->format) {
            $date = DateTime::createFromFormat($this->format, $value);

            return $date && $date->format($this->format) === $value;
        }

        return strtotime($value) !== false;
    }

    /**
     * Return validation error message.
     */
    public function message(): string
    {
        $replacer = [
            ':attribute' => $this->attribute
        ];

        $template = config(
            'constants.validator.error_message.date',
            self::DEFAULT_MESSAGE_TEMPLATE
        );

        return string_format($template, $replacer);
    }
}