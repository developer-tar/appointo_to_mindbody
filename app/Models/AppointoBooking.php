<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointoBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'appointment_id',
        'timestring',
        'email',
        'name',
        'phone',
        'quantity',
        'appointo_booking_id',
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }
}
