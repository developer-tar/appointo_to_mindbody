<?php 

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Jobs\UpdateShopifyCustomer;

class GetShopifyCustomer extends Command
{
    protected $signature = 'app:get-shopify-customer';
    protected $description = 'Fetch customers from Shopify and store them into DB';

    protected $baseUrl;
    protected $accessToken;
    protected $apiVersion;

    public function __construct()
    {
        parent::__construct();

        $user = User::first();
        $this->accessToken = $user->password;
        $this->baseUrl = $user->name;
        $this->apiVersion = config('constants.api_version');
    }

    public function handle()
    {
        $this->info("Starting shopify customer...");

        $pageInfo = null;
        $totalDispatched = 0;

        do {
            $result = $this->fetchCustomers($pageInfo);
            $customers = $result['customers'];
            $pageInfo = $result['next_page_info'];

            collect($customers)->chunk(50)->each(function ($chunk) use (&$totalDispatched) {
                UpdateShopifyCustomer::dispatch($chunk->toArray());
                $totalDispatched += count($chunk);
            });
        } while ($pageInfo);

        $this->info("Finished shopify customer");
    }

    private function fetchCustomers($pageInfo = null, $limit = 250)
    {
        $url = "https://{$this->baseUrl}/admin/api/{$this->apiVersion}/customers.json?limit={$limit}";

        if ($pageInfo) {
            $url .= "&page_info={$pageInfo}";
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get($url);

        $customers = $response->json('customers');
        $linkHeader = $response->header('Link');

        $nextPageInfo = null;
        if ($linkHeader && preg_match('/<([^>]+)>; rel="next"/', $linkHeader, $matches)) {
            parse_str(parse_url($matches[1], PHP_URL_QUERY), $query);
            $nextPageInfo = $query['page_info'] ?? null;
        }

        return [
            'customers' => $customers,
            'next_page_info' => $nextPageInfo,
        ];
    }
}
