<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MindbodyAppointment extends Model {
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'mindbody_client_id',
        'mindbody_appointment_id',
        'appointment_type_id',
        'session_type_id',
        'location_id',
        'staff_id',
        'starts_at',
        'json_data',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function shop() {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function client() {
        return $this->belongsTo(MindbodyClient::class, 'mindbody_client_id');
    }
}
