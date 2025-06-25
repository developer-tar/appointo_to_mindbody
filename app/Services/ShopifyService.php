<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class ShopifyService
{
    protected $baseUrl;
    protected $apiVersion;
    protected $accessToken;

    public function __construct()
    {
        $user = User::all();
        $this->accessToken = $user[0]->password;
        $this->baseUrl = $user[0]->name;
        $this->apiVersion = config('constants.api_version');
    }

    public function getProducts($pageInfo = null, $limit = 250)
    {
       
        $url = "https://{$this->baseUrl}/admin/api/{$this->apiVersion}/products.json?limit={$limit}";

        if ($pageInfo) {
            $url .= "&page_info={$pageInfo}";
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get($url);
        
        $products = $response->json('products');
     
        $linkHeader = $response->header('Link');

        $nextPageInfo = null;
        if ($linkHeader && preg_match('/<([^>]+)>; rel="next"/', $linkHeader, $matches)) {
            $nextPageUrl = $matches[1];
            parse_str(parse_url($nextPageUrl, PHP_URL_QUERY), $query);
            $nextPageInfo = $query['page_info'] ?? null;
        }
        return [    
            'products' => $products,
            'next_page_info' => $nextPageInfo,
        ];
    }
}
