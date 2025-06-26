<?php

namespace App\Jobs;

use App\Models\ShopifyCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateShopifyCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $customers;

    public function __construct(array $customers)
    {
        $this->customers = $customers;
    }

    public function handle(): void
    {
        foreach ($this->customers as $customer) {
            ShopifyCustomer::updateOrCreate(
                ['customer_id' => $customer['id']],
                [
                    'email' => $customer['email'],
                    'first_name' => $customer['first_name'],
                    'last_name' => $customer['last_name'],
                    'phone' => $customer['phone'],
                    'verified_email' => $customer['verified_email'],
                    'state' => $customer['state'],
                    'tags' => $customer['tags'],
                    'currency' => $customer['currency'],
                    'default_address' => $customer['default_address'] ?? null,
                    'shopify_created_at' => $customer['created_at'],
                    'shopify_updated_at' => $customer['updated_at'],
                    'json_data' => $customer,
                ]
            );
        }
    }
}
