<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'biographical_info' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'string', 'url'],

            'billing_address_1' => ['nullable', 'string', 'max:255'],
            'billing_address_2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_zip' => ['nullable', 'string', 'max:50'],
            'billing_country' => ['nullable', 'string', 'max:100'],

            'shipping_address_1' => ['nullable', 'string', 'max:255'],
            'shipping_address_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_zip' => ['nullable', 'string', 'max:50'],
            'shipping_country' => ['nullable', 'string', 'max:100'],

            'facebook' => ['nullable', 'string', 'url'],
            'twitter' => ['nullable', 'string', 'url'],
            'instagram' => ['nullable', 'string', 'url'],
            'linkedin' => ['nullable', 'string', 'url'],
            'youtube' => ['nullable', 'string', 'url'],
            'pinterest' => ['nullable', 'string', 'url'],
        ];
    }
}
