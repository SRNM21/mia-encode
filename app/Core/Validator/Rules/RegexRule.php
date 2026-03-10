<?php

namespace App\Core\Validator\Rules;

use App\Core\Contracts\Validation\Rule as RuleContract;

class RegexRule implements RuleContract
{
    /**
     * Regex pattern.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The field or attribute of the data.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Default message template.
     */
    protected const DEFAULT_MESSAGE_TEMPLATE = 'The :attribute format is invalid';

    /**
     * Store regex pattern.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Check if the value passes the validation rule.
     */
    public function passes($value, $attribute): bool
    {
        $this->attribute = $attribute;

        if (!is_string($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) === 1;
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
            'constants.validator.error_message.regex',
            self::DEFAULT_MESSAGE_TEMPLATE
        );

        return string_format($template, $replacer);
    }
}