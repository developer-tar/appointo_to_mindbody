<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointoMindbodySync extends Model {
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'appointo_booking_id',
        'mindbody_appointment_id',
        'source',
    ];

    public function shop() {
        return $this->belongsTo(User::class, 'shop_id');
    }

  
    public function mindbodyAppointment() {
        return $this->belongsTo(MindbodyAppointment::class);
    }
}
