<?php

namespace App\Providers;

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $appUrl = \App\Models\Setting::where('key', 'APP_URL')->value('value');
                if ($appUrl) {
                    config(['app.url' => $appUrl]);
                    
                    // Allow the app to dynamically resolve the host from the request for assets
                    // We don't forceRootUrl here so it won't break if accessed via localhost or IP
                    // Force HTTPS if the URL uses https
                    if (str_starts_with($appUrl, 'https://')) {
                        \Illuminate\Support\Facades\URL::forceScheme('https');
                    }
                }
                
                $appName = \App\Models\Setting::where('key', 'APP_NAME')->value('value');
                if ($appName) {
                    config(['app.name' => $appName]);
                }

                $appTimezone = \App\Models\Setting::where('key', 'APP_TIMEZONE')->value('value');
                if ($appTimezone) {
                    config(['app.timezone' => $appTimezone]);
                    date_default_timezone_set($appTimezone);
                }
            }
        } catch (\Exception $e) {
            // Ignore during migrations or when DB is unavailable
        }
    }
}
