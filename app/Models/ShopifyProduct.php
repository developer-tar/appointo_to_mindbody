<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model {
    protected $fillable = [
        "product_id",
        "title",
        "body_html",
        "vendor",
        "product_type",
        "shopify_created_at",
        "handle",
        "shopify_updated_at",
        "published_at",
        "template_suffix",
        "published_scope",
        "tags",
        "status",
        "admin_graphql_api_id",

        "variants",
        "options",
        "images",
        "image",
    ];
}
