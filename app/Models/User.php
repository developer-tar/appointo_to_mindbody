<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;

class User extends Authenticatable implements IShopModel
{
    use HasFactory, Notifiable, ShopModel;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    // Relationships to bookings and appointments
    public function appointoBookings()
    {
        return $this->hasMany(AppointoBooking::class, 'shop_id');
    }

    public function mindbodyClients()
    {
        return $this->hasMany(MindbodyClient::class, 'shop_id');
    }

    public function mindbodyAppointments()
    {
        return $this->hasMany(MindbodyAppointment::class, 'shop_id');
    }
}
