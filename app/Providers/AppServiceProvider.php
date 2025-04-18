<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Check if the current host differs from the configured APP_URL host
        $configuredHost = parse_url(config('app.url'), PHP_URL_HOST);
        $currentHost = Request::getHost();
        
        if ($currentHost && $configuredHost && $currentHost !== $configuredHost) {
            // We're accessing through a different host, update URLs accordingly
            $url = Request::getScheme() . '://' . $currentHost;
            
            // Force HTTPS for assets if the request is HTTPS
            if (Request::isSecure()) {
                URL::forceScheme('https');
            }
            
            // Set the URL in the config
            config(['app.url' => $url]);
            config(['app.asset_url' => $url]);
        }
    }
}
