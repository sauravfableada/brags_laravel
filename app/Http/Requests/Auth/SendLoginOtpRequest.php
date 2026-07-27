<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendLoginOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required_without:phone', 'email', 'exists:users,email'],
            'phone' => ['required_without:email', 'string', 'exists:user_details,phone'],
            'role' => ['nullable', 'string', 'in:admin,customer,seller'],
        ];
    }
}
