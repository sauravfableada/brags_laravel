<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandRegistrationProduct extends Model
{
    protected $fillable = [
        'brand_id',
        'product_identifier',
        'product_image',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
