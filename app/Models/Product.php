<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\PathHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'brand_id',
        'name',
        'slug',
        'short_description',
        'description',
        'asin',
        'primary_image',
        'gallery_images',
        'gallery_360_images',
        'restriction_type',
        'restriction_display_for',
        'restriction_purchase_for',
        'enable_custom_messages',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'gallery_360_images' => 'array',
        'restriction_display_for' => 'array',
        'restriction_purchase_for' => 'array',
        'enable_custom_messages' => 'boolean',
    ];

    protected function primaryImage(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? PathHelper::asset('storage/' . $value) : null,
        );
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
