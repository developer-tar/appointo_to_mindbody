<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAppointment extends Model {
    protected $fillable = [
        'appointment_id',
        'timestring',
        'email',
        'name',
        'phone',
        'is_sync',
        'source',
        'source_json_data',
        'client_id',
        'location_id',
        'session_type_id',
        'staff_id',
        'after_sync_json_data',
    ];
    public function scopeUnsynced($query) {
        return $query->where('is_sync', config('constants.sync.no'));
    }
}
