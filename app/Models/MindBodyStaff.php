<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MindBodyStaff extends Model {
    use SoftDeletes;

    protected $fillable = [
        'address',
        'appointment_instructor',
        'always_allow_double_booking',
        'bio',
        'city',
        'country',
        'email',
        'first_name',
        'display_name',
        'home_phone',
        'mindbody_staff_id',
        'independent_contractor',
        'is_male',
        'last_name',
        'mobile_phone',
        'name',
        'postal_code',
        'class_teacher',
        'sort_order',
        'state',
        'work_phone',
        'image_url',
        'class_assistant',
        'class_assistant2',
        'employment_start',
        'employment_end',
        'provider_ids',
        'rep',
        'rep2',
        'rep3',
        'rep4',
        'rep5',
        'rep6',
        'staff_settings',
        'appointments',
        'unavailabilities',
        'availabilities',
        'emp_id',
        'json_data'
    ];
    protected $casts = [
        // JSON
        'appointments' => 'array',
        'unavailabilities' => 'array',
        'availabilities' => 'array',
        'staff_settings' => 'array',
        'json_data' => 'array',

        // Booleans
        'appointment_instructor' => 'boolean',
        'always_allow_double_booking' => 'boolean',
        'independent_contractor' => 'boolean',
        'is_male' => 'boolean',
        'class_teacher' => 'boolean',
        'class_assistant' => 'boolean',
        'class_assistant2' => 'boolean',
        'rep' => 'boolean',
        'rep2' => 'boolean',
        'rep3' => 'boolean',
        'rep4' => 'boolean',
        'rep5' => 'boolean',
        'rep6' => 'boolean',

        // Dates (optional if using Carbon)
        'employment_start' => 'datetime',
        'employment_end' => 'datetime',
    ];
}
