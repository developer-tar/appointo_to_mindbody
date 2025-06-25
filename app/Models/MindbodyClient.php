<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MindbodyClient extends Model {
    use HasFactory;
    protected $fillable = [
        'shop_id',
        'mindbody_client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birthdate'
    ];

    public function shop() {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function appointments() {
        return $this->hasMany(MindbodyAppointment::class, 'mindbody_client_id');
    }
}
