<?php

namespace App\Http\Request;

use App\Http\Request\ValidationRequest;

class PasswordRequest extends ValidationRequest
{
    public function rules(): array
    {
        return [
            'current_password'      => ['required'],
            'new_password'          => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/'],
            'confirm_password'      => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'     => 'Current password is required.',
            'new_password.required'         => 'New password is required.',
            'confirm_password.required'     => 'Confirm password is required.',

            'new_password.regex'            => 'regex',
        ];
    }
}