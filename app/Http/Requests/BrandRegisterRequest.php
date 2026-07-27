<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BrandRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Step 1
            'brand_name' => 'required|string|max:255',
            'brand_logo' => 'required|image|max:2048',
            'trademark_office' => 'required|string|max:255',
            'trademark_registration_number' => 'required|string|max:255',
            'brand_description' => 'required|string',
            
            // Step 2
            'business_name' => 'required|string|max:255',
            'business_address' => 'required|string',
            'business_contact_email' => 'required|email|max:255|unique:users,email',
            'primary_contact_name' => 'required|string|max:255',
            'phone_number_country' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'password' => 'required|string|min:8|confirmed',
            
            // Step 3
            'manufacturing_locations' => 'required|string',
            'distribution_channels' => 'required|string',
            'authorized_resellers' => 'nullable|string',
            'product_supply_chain' => 'nullable|string',
            
            // Step 4
            'product_categories' => 'required|string',
            'products' => 'required|array|min:1|max:5',
            'products.*.identifier' => 'required|string|max:255',
            'products.*.image' => 'required|image|max:2048',
            
            // Step 5
            'sell_under_own_brand' => 'required|boolean',
            'seller_email' => 'nullable|email|max:255',
            'store_url' => 'nullable|url|max:255',
            'approve_resellers' => 'required|boolean',
            'additional_documentation' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }
}
