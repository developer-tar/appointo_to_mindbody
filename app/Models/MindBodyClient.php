<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MindBodyClient extends Model {
    use SoftDeletes;
    
    protected $fillable = [
        // Basic client info
        'mindbody_client_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'mobile_phone',
        'home_phone',
        'work_phone',
        'work_extension',
        'gender',
        'birth_date',
        'country',
        'city',
        'state',
        'postal_code',
        'address_line1',
        'address_line2',
        'photo_url',

        // Emergency contact
        'emergency_contact_info_name',
        'emergency_contact_info_email',
        'emergency_contact_info_phone',
        'emergency_contact_info_relationship',

        // Membership & status
        'status',
        'active',
        'is_company',
        'is_prospect',
        'membership_icon',
        'prospect_stage',

        // Preferences & communication
        'appointment_gender_preference',
        'send_account_emails',
        'send_account_texts',
        'send_promotional_emails',
        'send_promotional_texts',
        'send_schedule_emails',
        'send_schedule_texts',

        // Dates
        'creation_date',
        'first_appointment_date',
        'first_class_date',
        'last_modified_date_time',

        // Other details
        'unique_id',
        'notes',
        'last_formula_notes',
        'account_balance',
        'referred_by',
        'locker_number',
        'action',
        'mobile_provider',
        'red_alert',
        'yellow_alert',

        // Nested/JSON structures
        'custom_client_fields',
        'client_credit_card',
        'sales_reps',
        'home_location',
        'suspension_info',
        'client_indexes',
        'client_relationships',
        'liability',
        'liability_release',

        // Optional for storing extra/future fields
        'json_data',
    ];

    protected $casts = [
        // JSON
        'custom_client_fields'   => 'array',
        'client_credit_card'     => 'array',
        'sales_reps'             => 'array',
        'home_location'          => 'array',
        'suspension_info'        => 'array',
        'client_indexes'         => 'array',
        'client_relationships'   => 'array',
        'liability'              => 'array',
        'json_data'              => 'array',

        // Booleans
        'active'                 => 'boolean',
        'is_company'             => 'boolean',
        'is_prospect'            => 'boolean',
        'send_account_emails'    => 'boolean',
        'send_account_texts'     => 'boolean',
        'send_promotional_emails' => 'boolean',
        'send_promotional_texts' => 'boolean',
        'send_schedule_emails'   => 'boolean',
        'send_schedule_texts'    => 'boolean',

        // DateTime
        'birth_date'             => 'datetime',
        'creation_date'          => 'datetime',
        'first_appointment_date' => 'datetime',
        'first_class_date'       => 'datetime',
        'last_modified_date_time' => 'datetime',

        // Float
        'account_balance'        => 'float',
    ];


    public function shop() {
        return $this->belongsTo(User::class, 'shop_id');
    }

    // If you're using guarded or fillable, keep them here...

    protected $appends = ['name']; // 👈 This makes full_name available in arrays and JSON

    // Accessor
    public function getNameAttribute()
{
    return collect([$this->first_name, $this->middle_name, $this->last_name])
        ->filter()
        ->implode(' ');
}
}
