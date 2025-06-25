<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\ShopifyLog;
use App\Models\ShopifyProduct;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\Log;

class GetShopifyProduct extends Command {
    protected $signature = 'app:get-shopify-product';
    protected $description = 'Fetch products from Shopify and store them into DB';

    public function __construct(ShopifyService $shopify) {
        parent::__construct();
        $this->shopify = $shopify;
    }

    public function handle() {
        $this->info("Starting product sync...");

        $pageInfo = null;
        $totalSaved = 0;

        do {
            $result = $this->shopify->getProducts($pageInfo);
            $products = $result['products'];
            $pageInfo = $result['next_page_info'];

            foreach ($products as $product) {
                ShopifyProduct::updateOrCreate(
                    ['product_id' => $product['id']],
                    [
                        'title' => $product['title'],
                        'body_html' => $product['body_html'],
                        'vendor' => $product['vendor'],
                        'product_type' => $product['product_type'],
                        'shopify_created_at' => $product['created_at'],
                        'handle' => $product['handle'],
                        'shopify_updated_at' => $product['updated_at'],
                        'published_at' => $product['published_at'],
                        'template_suffix' => $product['template_suffix'],
                        'published_scope' => $product['published_scope'],
                        'tags' => $product['tags'],
                        'status' => $product['status'],
                        'admin_graphql_api_id' => $product['admin_graphql_api_id'],
                        'variants' => json_encode($product['variants']),
                        'options' => json_encode($product['variants']),
                        'images' => json_encode($product['images']),
                        'image' => json_encode($product['image']),
                    ]
                );
                $totalSaved++;
            }

            $this->info("Fetched and saved " . count($products) . " products...");
        } while ($pageInfo);

        $this->info("Total products saved: $totalSaved");
    }
}
