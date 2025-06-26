<?php

namespace App\Jobs;

use App\Models\MindBodyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateMindBodyClient implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $clients;

    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    public function handle(): void
    {
        foreach ($this->clients as $client) {
            try {
                // Sanitize array fields
                $client['CustomClientFields']   = json_decode(json_encode($client['CustomClientFields'] ?? []), true);
                $client['ClientCreditCard']     = json_decode(json_encode($client['ClientCreditCard'] ?? null), true);
                $client['SalesReps']            = json_decode(json_encode($client['SalesReps'] ?? []), true);
                $client['HomeLocation']         = json_decode(json_encode($client['HomeLocation'] ?? []), true);
                $client['SuspensionInfo']       = json_decode(json_encode($client['SuspensionInfo'] ?? []), true);
                $client['ClientIndexes']        = json_decode(json_encode($client['ClientIndexes'] ?? []), true);
                $client['ClientRelationships']  = json_decode(json_encode($client['ClientRelationships'] ?? []), true);
                $client['Liability']            = json_decode(json_encode($client['Liability'] ?? []), true);
                $client['json_data']            = json_decode(json_encode($client), true);

                MindBodyClient::updateOrCreate(
                    ['unique_id' => $client['UniqueId']],
                    [
                        'mindbody_client_id' => $client['Id'],
                        'appointment_gender_preference'       => $client['AppointmentGenderPreference'] ?? null,
                        'birth_date'                          => $client['BirthDate'] ?? null,
                        'country'                             => $client['Country'] ?? null,
                        'creation_date'                       => $client['CreationDate'] ?? null,
                        'custom_client_fields'                => $client['CustomClientFields'],
                        'client_credit_card'                  => $client['ClientCreditCard'],
                        'first_appointment_date'              => $client['FirstAppointmentDate'] ?? null,
                        'first_class_date'                    => $client['FirstClassDate'] ?? null,
                        'first_name'                          => $client['FirstName'] ?? null,
                        'last_name'                           => $client['LastName'] ?? null,
                        'middle_name'                         => $client['MiddleName'] ?? null,
                        'is_company'                          => $client['IsCompany'] ?? null,
                        'is_prospect'                         => $client['IsProspect'] ?? null,
                        'liability_release'                   => $client['LiabilityRelease'] ?? null,
                        'membership_icon'                     => $client['MembershipIcon'] ?? null,
                        'mobile_provider'                     => $client['MobileProvider'] ?? null,
                        'notes'                               => $client['Notes'] ?? null,
                        'state'                               => $client['State'] ?? null,
                        'last_modified_date_time'             => $client['LastModifiedDateTime'] ?? null,
                        'red_alert'                           => $client['RedAlert'] ?? null,
                        'yellow_alert'                        => $client['YellowAlert'] ?? null,
                        'prospect_stage'                      => $client['ProspectStage'] ?? null,
                        'email'                               => $client['Email'] ?? null,
                        'mobile_phone'                        => $client['MobilePhone'] ?? null,
                        'home_phone'                          => $client['HomePhone'] ?? null,
                        'work_phone'                          => $client['WorkPhone'] ?? null,
                        'account_balance'                     => $client['AccountBalance'] ?? null,
                        'address_line1'                       => $client['AddressLine1'] ?? null,
                        'address_line2'                       => $client['AddressLine2'] ?? null,
                        'city'                                => $client['City'] ?? null,
                        'postal_code'                         => $client['PostalCode'] ?? null,
                        'work_extension'                      => $client['WorkExtension'] ?? null,
                        'referred_by'                         => $client['ReferredBy'] ?? null,
                        'photo_url'                           => $client['PhotoUrl'] ?? null,
                        'emergency_contact_info_name'         => $client['EmergencyContactInfoName'] ?? null,
                        'emergency_contact_info_email'        => $client['EmergencyContactInfoEmail'] ?? null,
                        'emergency_contact_info_phone'        => $client['EmergencyContactInfoPhone'] ?? null,
                        'emergency_contact_info_relationship' => $client['EmergencyContactInfoRelationship'] ?? null,
                        'gender'                              => $client['Gender'] ?? null,
                        'last_formula_notes'                  => $client['LastFormulaNotes'] ?? null,
                        'active'                              => $client['Active'] ?? null,
                        'status'                              => $client['Status'] ?? null,
                        'action'                              => $client['Action'] ?? null,
                        'send_account_emails'                 => $client['SendAccountEmails'] ?? null,
                        'send_account_texts'                  => $client['SendAccountTexts'] ?? null,
                        'send_promotional_emails'             => $client['SendPromotionalEmails'] ?? null,
                        'send_promotional_texts'              => $client['SendPromotionalTexts'] ?? null,
                        'send_schedule_emails'                => $client['SendScheduleEmails'] ?? null,
                        'send_schedule_texts'                 => $client['SendScheduleTexts'] ?? null,
                        'locker_number'                       => $client['LockerNumber'] ?? null,
                        'sales_reps'                          => $client['SalesReps'],
                        'home_location'                       => $client['HomeLocation'],
                        'suspension_info'                     => $client['SuspensionInfo'],
                        'client_indexes'                      => $client['ClientIndexes'],
                        'client_relationships'                => $client['ClientRelationships'],
                        'liability'                           => $client['Liability'],
                        'json_data'                           => $client['json_data'],
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Failed to update MindBody client', [
                    'client_id' => $client['UniqueId'] ?? 'unknown',
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
