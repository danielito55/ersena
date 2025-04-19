<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as RequestFacade; // Use alias to avoid conflict
use Illuminate\Http\Request; // Import the Request class for constants

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
        // Only attempt to handle proxied connections if NOT in the local environment.
        // For local development, rely strictly on the .env APP_URL.
        if (!$this->app->environment('local')) {
             $this->handleProxiedConnections();
        }
    }

    /**
     * Dynamically configure URLs and session settings for proxied connections (e.g., ngrok, Docker).
     */
    private function handleProxiedConnections(): void
    {
        // Trust all proxies when running locally - adjust for production later
        // Ensure TrustProxies middleware is configured correctly too.
        // Note: This block might still run if environment is not 'local',
        // ensure TrustProxies middleware is appropriately configured for production/staging.
        // if ($this->app->environment('local')) { // This check is now redundant due to the check in boot()
            RequestFacade::setTrustedProxies(
                ['*'], // Consider restricting this in production
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB // Also include AWS ELB header if applicable
            );
        // } // Redundant closing brace

        // Get the configured host from APP_URL
        $configuredHost = parse_url(config('app.url'), PHP_URL_HOST);
        $configuredScheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';

        // Get the current host and scheme from the request (respecting trusted proxy headers)
        $currentHost = RequestFacade::getHost();
        $currentScheme = RequestFacade::getScheme(); // Should be 'https' if TrustProxies works

        // Check if we're accessing through a different host or scheme than configured in .env
        if ($currentHost && $configuredHost && ($currentHost !== $configuredHost || $currentScheme !== $configuredScheme)) {

            // Build the full URL with the correct scheme and host
            $url = $currentScheme . '://' . $currentHost;

            // Check if there's a specific port in the request
            $port = RequestFacade::getPort();

            // Append non-standard ports if necessary
            if ($port &&
                (($currentScheme === 'http' && $port != 80) ||
                 ($currentScheme === 'https' && $port != 443))) {
                $url .= ':' . $port;
            }

            // Force the scheme for URL generation if accessed via HTTPS
            if ($currentScheme === 'https') {
                URL::forceScheme('https');
                Config::set('session.secure', true);
            } else {
                 Config::set('session.secure', false);
            }

            // Update application URLs
            Config::set('app.url', $url);
            Config::set('app.asset_url', $url); // Ensure asset URL also uses the detected URL

            // Dynamically set the session domain to the current host
            Config::set('session.domain', $currentHost);


            // Help debugging by logging URL configuration
            if (config('app.debug')) {
                Log::info('Proxied connection detected, updating configuration:', [
                    'originalAppUrl' => config('app.url'), // Note: This might log the *already updated* URL here
                    'currentHost' => $currentHost,
                    'currentScheme' => $currentScheme,
                    'configuredHost' => $configuredHost, // Original host from .env
                    'updatedAppUrl' => $url,
                    'updatedAssetUrl' => $url,
                    'sessionSecure' => config('session.secure'),
                    'sessionDomain' => config('session.domain'),
                ]);
            }
        }
    }
}
