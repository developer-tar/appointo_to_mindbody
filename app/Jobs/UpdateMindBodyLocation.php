<?php

namespace App\Jobs;

use App\Models\MindBodyLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class UpdateMindBodyLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $locations;

    public function __construct(array $locations)
    {
        $this->locations = $locations;
    }

    public function handle(): void
    {
        foreach ($this->locations as $location) {
            try {
                MindBodyLocation::updateOrCreate(
                    ['mindbody_location_id' => $location['Id']],
                    [
                        'name' => $location['Name'] ?? null,
                        'address' => $location['Address'] ?? null,
                        'address2' => $location['Address2'] ?? null,
                        'city' => $location['City'] ?? null,
                        'state_prov_code' => $location['StateProvCode'] ?? null,
                        'postal_code' => $location['PostalCode'] ?? null,
                        'phone' => $location['Phone'] ?? null,
                        'phone_extension' => $location['PhoneExtension'] ?? null,
                        'business_description' => $location['BusinessDescription'] ?? null,
                        'description' => $location['Description'] ?? null,
                        'has_classes' => $location['HasClasses'] ?? false,
                        'latitude' => $location['Latitude'] ?? null,
                        'longitude' => $location['Longitude'] ?? null,
                        'tax1' => $location['Tax1'] ?? null,
                        'tax2' => $location['Tax2'] ?? null,
                        'tax3' => $location['Tax3'] ?? null,
                        'tax4' => $location['Tax4'] ?? null,
                        'tax5' => $location['Tax5'] ?? null,
                        'total_number_of_ratings' => $location['TotalNumberOfRatings'] ?? 0,
                        'average_rating' => $location['AverageRating'] ?? 0.0,
                        'total_number_of_deals' => $location['TotalNumberOfDeals'] ?? 0,
                        'additional_image_urls' => $location['AdditionalImageURLs'] ?? [],
                        'amenities' => $location['Amenities'] ?? [],
                        'json_data' => $location,
                    ]
                );
            } catch (\Throwable $e) {
                Log::error('Failed to update MindBody location', [
                    'location_id' => $location['Id'] ?? 'unknown',
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }
}
