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

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
