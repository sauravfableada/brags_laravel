<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'nickname',
        'phone',
        'company',
        'website',
        'biographical_info',
        'profile_picture',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'pinterest',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
