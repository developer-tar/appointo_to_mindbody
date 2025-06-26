<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyCustomer extends Model {
    protected $fillable = [
        'customer_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'verified_email',
        'state',
        'tags',
        'currency',
        'default_address',
        'shopify_created_at',
        'shopify_updated_at',
        'json_data'
    ];

    protected $casts = [
        'default_address' => 'array',
        'shopify_created_at' => 'datetime',
        'shopify_updated_at' => 'datetime',
        'json_data' => 'array',
    ];
}
