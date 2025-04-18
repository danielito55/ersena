<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
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
        // Handle proxied connections including LocalTunnel
        $this->handleProxiedConnections();
    }
    
    /**
     * Handle proxied connections including LocalTunnel
     */
    private function handleProxiedConnections(): void
    {
        // Get the configured host from APP_URL
        $configuredHost = parse_url(config('app.url'), PHP_URL_HOST);
        
        // Get the current host from the request
        $currentHost = Request::getHost();
        
        // Check if we're accessing through a different host (like LocalTunnel)
        if ($currentHost && $configuredHost && $currentHost !== $configuredHost) {
            // Build the full URL with scheme and host
            $scheme = Request::isSecure() ? 'https' : 'http';
            $url = $scheme . '://' . $currentHost;
            
            // Force HTTPS for assets if the request is HTTPS
            if (Request::isSecure()) {
                URL::forceScheme('https');
            }
            
            // Check if there's a specific port in the request
            $port = Request::getPort();
            
            // Only append non-standard ports to the URL
            // (For LocalTunnel the port is typically handled automatically)
            if ($port && 
                (($scheme === 'http' && $port != 80) || 
                 ($scheme === 'https' && $port != 443))) {
                // Non-standard port, add it to the URL
                $url .= ':' . $port;
            }
            
            // Update application URLs
            config(['app.url' => $url]);
            config(['app.asset_url' => $url]);
            
            // Help debugging by logging URL configuration
            if (config('app.debug')) {
                Log::info('Proxied connection detected', [
                    'currentHost' => $currentHost,
                    'configuredHost' => $configuredHost, 
                    'updatedUrl' => $url
                ]);
            }
        }
    }
}
