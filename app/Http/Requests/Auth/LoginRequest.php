<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // If login field is not present but email is (for AuthController), use email
        if ($this->has('email') && !$this->has('login')) {
            return [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ];
        }

        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
