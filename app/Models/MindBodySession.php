<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MindBodySession extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'mindbody_session_id',
        'type',
        'default_time_length',
        'staff_time_length',
        'name',
        'online_description',
        'num_deducted',
        'program_id',
        'category',
        'category_id',
        'subcategory',
        'subcategory_id',
        'available_for_add_on',
        'json_data',
    ];

    protected $casts = [
        'default_time_length' => 'integer',
        'staff_time_length' => 'integer',
        'num_deducted' => 'integer',
        'program_id' => 'integer',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'available_for_add_on' => 'boolean',
        'json_data' => 'array',
    ];
}
