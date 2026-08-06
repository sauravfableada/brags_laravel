<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cast string boolean values from form-data to actual booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $booleans = ['requires_approval', 'disable_referrals'];
        $data = [];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $data[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $this->input($field);
            }
        }
        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:255'],
            'slug'                   => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'parent_id'              => ['nullable', 'exists:categories,id'],
            'description'            => ['nullable', 'string'],
            'display_type'           => ['nullable', 'string', 'in:default,products,subcategories,both'],
            'thumbnail'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'requires_approval'      => ['nullable', 'boolean'],
            'content_restriction'    => ['nullable', 'string', 'in:none,logged_in,subscription'],
            'restriction_message'    => ['nullable', 'string'],
            'referral_rate_type'     => ['nullable', 'string', 'in:fixed,percentage'],
            'referral_rate'          => ['nullable', 'numeric', 'min:0'],
            'disable_referrals'      => ['nullable', 'boolean'],
            'category_icon'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:1024'],
            'page_title_background'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
