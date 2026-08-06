<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Helpers\PathHelper;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'display_type',
        'thumbnail',
        'requires_approval',
        'content_restriction',
        'restriction_message',
        'referral_rate_type',
        'referral_rate',
        'disable_referrals',
        'category_icon',
        'page_title_background',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'disable_referrals' => 'boolean',
        'referral_rate'     => 'float',
    ];

    /**
     * Get the full URL for the thumbnail.
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? PathHelper::asset('storage/' . $value) : null,
        );
    }

    /**
     * Get the full URL for the category icon.
     */
    protected function categoryIcon(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? PathHelper::asset('storage/' . $value) : null,
        );
    }

    /**
     * Get the full URL for the page title background.
     */
    protected function pageTitleBackground(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? PathHelper::asset('storage/' . $value) : null,
        );
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
