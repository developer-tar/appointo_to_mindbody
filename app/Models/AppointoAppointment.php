<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointoAppointment extends Model {
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'activate',
        'product_uuid',
        'duration_uuid',
        'product_detail_id',
        'name',
        'price',
        'currency',
        'appointment_config',
        'team_members',
        'groups',
        'weekly_availabilities',
        'overridden_availabilities',
        'custom_fields'
    ];
    protected $casts = [
        'appointment_config'        => 'array',
        'team_members'              => 'array',
        'groups'                    => 'array',
        'weekly_availabilities'     => 'array',
        'overridden_availabilities' => 'array',
        'custom_fields'             => 'array',
    ];
}
