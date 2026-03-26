<?php

namespace App\Core\Validator;

use App\Core\Support\Session;
use App\Core\Support\MessageBag;
use App\Core\Validator\Rules\InRule;
use App\Core\Validator\Rules\MaxRule;
use App\Core\Validator\Rules\MinRule;
use App\Core\Validator\Rules\StringRule;
use App\Core\Validator\Rules\IntegerRule;
use App\Core\Validator\Rules\RequiredRule;
use App\Core\Validator\Rules\DateRule;
use App\Core\Validator\Rules\RegexRule;
use App\Core\Validator\Exceptions\ValidationException;
use App\Core\Validator\Rules\EmailRule;

class Validator 
{
    /**
     * Rule class mapper.
     *
     * @var array
     */
    protected static $rule_map = [
        'required' => RequiredRule::class,
        'string' => StringRule::class,
        'integer' => IntegerRule::class,
        'min' => MinRule::class,
        'max' => MaxRule::class,
        'in' => InRule::class,
        'date' => DateRule::class,
        'regex' => RegexRule::class,
        'email' => EmailRule::class,
    ];

    /**
     * The data under validation.
     *
     * @var array
     */
    protected static $data;

    /**
     * The MessageBag instance.
     *
     * @var \App\Core\Support\MessageBag
     */
    protected static $message;

    /**
     * Strict flag that determine if the validation should return early if 
     * the first field validation is failed.
     *
     * @var bool
     */
    protected static $strict;

    /**
     * Rules flag that determine if the validation should return early if 
     * the first rule fails in the validation process.
     *
     * @var bool
     */
    protected static $all_rules;

    /**
     * Custom messages from successor class
     *
     * @var array
     */
    protected static $custom_messages;

    /**
     * Determine if the data passes the validation rules.
     *
     * @param array $data
     * @param array $rules
     * @throws \App\Core\Validator\Exceptions\ValidationException
     * @return void
     */
    public static function validate(
        $data = [],
        $rules = [],
        $messages = [],
        $strict = false,
        $all_rules = false
    ) {
        self::$message = new MessageBag();
        self::$data = $data;
        self::$strict = $strict;
        self::$all_rules = $all_rules;
        self::$custom_messages = $messages;

        foreach ($rules as $field => $rule_set) 
        {
            self::validateFields($field, $rule_set);

            if (self::$strict) break;
        }

        if (!self::$message->isEmpty())
        {
            Session::set('errors', self::$message->getMessages());
            return;
        }
    }

    /**
     * Validate a given attribute against a rule.
     *
     * @param string $field
     * @param array $rule_set
     * @throws \App\Core\Validator\Exceptions\ValidationException
     * @return void
     */
    private static function validateFields($field, $rule_set)
    {
        $valueExists = array_key_exists($field, self::$data);
        $value = self::$data[$field] ?? null;

        $isOptional = in_array('optional', $rule_set);
        $isNullable = in_array('nullable', $rule_set);

        // Skip validation if optional and field not present
        if ($isOptional && (!$valueExists || $value === null || $value === '')) {
            return;
        }

        // Skip validation if nullable and value is null
        if ($value === null && $isNullable) {
            return;
        }

        // Remove optional/nullable from rule set before actual validation
        $rule_set = array_filter(
            $rule_set,
            fn($rule) => !in_array($rule, ['optional', 'nullable'])
        );

        foreach ($rule_set as $rule) 
        {
            [$rule_name, $rule_param] = self::parseRule($rule);

            // Handle invalid rule
            if (!self::isRuleExist($rule_name))
            {
                throw new ValidationException("The rule '$rule_name' does not exist.");
            }

            // If the rule has parameters like (rule:param) we initialize the instance
            // with the parameter to invoke the constructor, otherwise, we simply initialize
            // the class without the parameter.
            $rule_instance = ($rule_param !== null)
                ? new self::$rule_map[$rule_name]($rule_param)
                : new self::$rule_map[$rule_name];

            $value = self::$data[$field] ?? null;

            // Store failed validation in message bag.
            if (!$rule_instance->passes($value, $field))
            {
                $message = self::resolveMessage($field, $rule_name, $rule_instance->message());
                self::$message->add($field, $message);
                
                if (!self::$all_rules) return;
            }
        }
    }

    private static function resolveMessage($field, $rule, $default)
    {
        $key = "{$field}.{$rule}";

        if (isset(self::$custom_messages[$key])) 
        {
            return self::$custom_messages[$key];
        }

        if (isset(self::$custom_messages[$field])) 
        {
            return self::$custom_messages[$field];
        }

        return $default;
    }

    /**
     * Extract the rule name and parameters from a rule
     *
     * @param string $rule
     * @return array
     */
    private static function parseRule($rule)
    {
        $rule_part = explode(':', $rule, 2);
        return [$rule_part[0], $rule_part[1] ?? null];
    }

    /**
     * Check if the rule name given is a valid rule
     *
     * @param string $rule
     * @return boolean
     */
    private static function isRuleExist($rule)
    {
        return key_exists($rule, self::$rule_map);
    }
}