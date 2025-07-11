<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="shopify-api-key" content="{{ \Osiset\ShopifyApp\Util::getShopifyConfig('api_key', $shopDomain ?? Auth::user()->name ) }}"/>
        <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>

        <title>{{ config('shopify-app.app_name') }}</title>
        @yield('styles')
    </head>

    <body>
        <div class="app-wrapper">
            <div class="app-content">
                <main role="main">

                    <ui-nav-menu>
                        <a href="/homeeee">Home</a>
                        <a href="/settings">Settings</a>
                        <a href="/settings-1">Settings 1</a>
                        <a href="/settings-2">Settings 2</a>
                        <a href="/settings-3">Settings 3</a>
                    </ui-nav-menu>

                    @yield('content')
                </main>
            </div>
        </div>

        @if(\Osiset\ShopifyApp\Util::useNativeAppBridge())
            @include('shopify-app::partials.token_handler')
        @endif
        @yield('scripts')
    </body>
</html>
