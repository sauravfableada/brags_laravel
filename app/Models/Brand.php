<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'user_id',
        'brand_name',
        'brand_logo',
        'trademark_office',
        'trademark_registration_number',
        'brand_description',
        'business_name',
        'business_address',
        'business_contact_email',
        'primary_contact_name',
        'phone_number_country',
        'phone_number',
        'website_url',
        'manufacturing_locations',
        'distribution_channels',
        'authorized_resellers',
        'product_supply_chain',
        'product_categories',
        'sell_under_own_brand',
        'seller_email',
        'store_url',
        'approve_resellers',
        'additional_documentation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrationProducts()
    {
        return $this->hasMany(BrandRegistrationProduct::class);
    }
}
