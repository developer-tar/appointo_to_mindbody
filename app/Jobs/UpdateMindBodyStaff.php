<?php

namespace App\Jobs;

use App\Models\MindBodyStaff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateMindBodyStaff implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $staffs;

    public function __construct(array $staffs) {
        $this->staffs = $staffs;
    }

    public function handle(): void {
        foreach ($this->staffs as $staff) {
            try {
                // Normalize complex fields (convert to arrays safely)
                $staff['StaffSettings']   = json_decode(json_encode($staff['StaffSettings'] ?? []), true);
                $staff['Appointments']    = json_decode(json_encode($staff['Appointments'] ?? []), true);
                $staff['Unavailabilities'] = json_decode(json_encode($staff['Unavailabilities'] ?? []), true);
                $staff['Availabilities']  = json_decode(json_encode($staff['Availabilities'] ?? []), true);
                $staff['json_data']       = json_decode(json_encode($staff), true);

                MindBodyStaff::updateOrCreate(
                    ['mindbody_staff_id' => $staff['Id']],
                    [
                        'address'                 => $staff['Address'] ?? null,
                        'appointment_instructor' => $staff['AppointmentInstructor'] ?? null,
                        'always_allow_double_booking' => $staff['AlwaysAllowDoubleBooking'] ?? null,
                        'bio'                     => $staff['Bio'] ?? null,
                        'city'                    => $staff['City'] ?? null,
                        'country'                 => $staff['Country'] ?? null,
                        'email'                   => $staff['Email'] ?? null,
                        'first_name'              => $staff['FirstName'] ?? null,
                        'display_name'            => $staff['DisplayName'] ?? null,
                        'home_phone'              => $staff['HomePhone'] ?? null,
                        'independent_contractor'  => $staff['IndependentContractor'] ?? null,
                        'is_male'                 => $staff['IsMale'] ?? null,
                        'last_name'               => $staff['LastName'] ?? null,
                        'mobile_phone'            => $staff['MobilePhone'] ?? null,
                        'name'                    => $staff['Name'] ?? null,
                        'postal_code'             => $staff['PostalCode'] ?? null,
                        'class_teacher'           => $staff['ClassTeacher'] ?? null,
                        'sort_order'              => $staff['SortOrder'] ?? null,
                        'state'                   => $staff['State'] ?? null,
                        'work_phone'              => $staff['WorkPhone'] ?? null,
                        'image_url'               => $staff['ImageUrl'] ?? null,
                        'class_assistant'         => $staff['ClassAssistant'] ?? null,
                        'class_assistant2'        => $staff['ClassAssistant2'] ?? null,
                        'employment_start'        => $staff['EmploymentStart'] ?? null,
                        'employment_end'          => $staff['EmploymentEnd'] ?? null,
                        'provider_ids'            => $staff['ProviderIDs'] ?? null,
                        'rep'                     => $staff['Rep'] ?? null,
                        'rep2'                    => $staff['Rep2'] ?? null,
                        'rep3'                    => $staff['Rep3'] ?? null,
                        'rep4'                    => $staff['Rep4'] ?? null,
                        'rep5'                    => $staff['Rep5'] ?? null,
                        'rep6'                    => $staff['Rep6'] ?? null,
                        'emp_id'                  => $staff['EmpID'] ?? null,

                        // JSON columns
                        'staff_settings'          => $staff['StaffSettings'],
                        'appointments'            => $staff['Appointments'],
                        'unavailabilities'        => $staff['Unavailabilities'],
                        'availabilities'          => $staff['Availabilities'],

                        // Raw payload
                        'json_data'               => $staff['json_data'],
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Failed to update MindBody staff', [
                    'staff_id' => $staff['Id'] ?? 'unknown',
                    'message'  => $e->getMessage(),
                    'trace'    => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
