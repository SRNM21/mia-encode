<?php

namespace App\Http\Request;

use App\Core\Validator\Validator;

abstract class ValidationRequest extends Request
{
    /**
     * Run validation automatically after request capture.
     */
    public function __construct(
        array $get,
        array $post,
        array $request,
        array $files,
        array $cookies,
        array $server
    ) {
        parent::__construct($get, $post, $request, $files, $cookies, $server);

        $this->validateResolved();
    }

    /**
     * Validation rules definition.
     */
    abstract public function rules(): array;

    /**
     * Determine strict validation behavior.
     */
    public function strict(): bool
    {
        return false;
    }

    /**
     * Determine if all rules should run.
     */
    public function allRules(): bool
    {
        return false;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Execute validation.
     */
    protected function validateResolved(): void
    {
        Validator::validate(
            $this->posts(),
            $this->rules(),
            $this->messages(),
            $this->strict(),
            $this->allRules()
        );
    }
}