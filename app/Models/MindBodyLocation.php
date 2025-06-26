<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MindBodyLocation extends Model
{
    protected $fillable = [
        'mindbody_location_id',
        'name',
        'address',
        'address2',
        'city',
        'state_prov_code',
        'postal_code',
        'phone',
        'phone_extension',
        'business_description',
        'description',
        'has_classes',
        'latitude',
        'longitude',
        'tax1',
        'tax2',
        'tax3',
        'tax4',
        'tax5',
        'total_number_of_ratings',
        'average_rating',
        'total_number_of_deals',
        'additional_image_urls',
        'amenities',
        'json_data',
    ];

    protected $casts = [
        'has_classes' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'tax1' => 'float',
        'tax2' => 'float',
        'tax3' => 'float',
        'tax4' => 'float',
        'tax5' => 'float',
        'average_rating' => 'float',
        'additional_image_urls' => 'array',
        'amenities' => 'array',
        'json_data' => 'array',
    ];
}
