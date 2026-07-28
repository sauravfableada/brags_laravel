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
        return [
            'login' => ['required_without:email_username', 'string'],
            'email_username' => ['required_without:login', 'string'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string', 'in:admin,customer,seller,brand,bragger'],
            'fcm_token' => ['nullable', 'string'],
        ];
    }
}
