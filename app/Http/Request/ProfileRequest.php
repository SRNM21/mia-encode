<?php

namespace App\Http\Request;

use App\Http\Request\ValidationRequest;

class ProfileRequest extends ValidationRequest
{
    public function rules(): array
    {
        return [
            'username'      => ['required', 'min:4', 'max:20'],
            'email'         => ['required', 'email'],
        ];
    }
}