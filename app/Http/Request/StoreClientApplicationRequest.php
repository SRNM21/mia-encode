<?php

namespace App\Http\Request;

use App\Http\Request\ValidationRequest;

class StoreClientApplicationRequest extends ValidationRequest
{
    public function rules(): array
    {
        return [
            'firstname'     => ['required', 'string'],
            'middlename'    => ['optional', 'string'],
            'lastname'      => ['required', 'string'],
            'birthdate'     => ['required', 'date:m/d/Y'],
            'mobile'        => ['required', 'regex:/^09\d{9}$/'],
            'agent'         => ['required'],
            'banks'         => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required'    => 'First name is required.',
            'firstname.string'      => 'First name must contain only letters and spaces.',

            'middlename.required'   => 'Middle name is required.',
            'middlename.string'     => 'Middle name must contain only letters and spaces.',

            'lastname.required'     => 'Last name is required.',
            'lastname.string'       => 'Last name must contain only letters and spaces.',

            'birthdate.required'    => 'Birthdate is required.',
            'birthdate.date'        => 'Invalid birthdate format.',

            'mobile.required'       => 'Mobile number is required.',
            'mobile.regex'          => 'Mobile number must be 11 digits and start with 09.',

            'agent'                 => 'Agent is required.',
            'banks'                 => 'Please select at least one bank.',
        ];
    }
}