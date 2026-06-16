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
                    \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
                    
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

                // Dynamic Mail Settings
                $mailSettings = \App\Models\Setting::whereIn('key', [
                    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 
                    'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'
                ])->pluck('value', 'key');

                if ($mailSettings->isNotEmpty()) {
                    if (isset($mailSettings['MAIL_MAILER'])) config(['mail.default' => $mailSettings['MAIL_MAILER']]);
                    if (isset($mailSettings['MAIL_HOST'])) config(['mail.mailers.smtp.host' => $mailSettings['MAIL_HOST']]);
                    if (isset($mailSettings['MAIL_PORT'])) config(['mail.mailers.smtp.port' => $mailSettings['MAIL_PORT']]);
                    if (isset($mailSettings['MAIL_USERNAME'])) config(['mail.mailers.smtp.username' => $mailSettings['MAIL_USERNAME']]);
                    if (isset($mailSettings['MAIL_PASSWORD'])) {
                        try {
                            config(['mail.mailers.smtp.password' => \Illuminate\Support\Facades\Crypt::decryptString($mailSettings['MAIL_PASSWORD'])]);
                        } catch (\Exception $e) {
                            config(['mail.mailers.smtp.password' => $mailSettings['MAIL_PASSWORD']]);
                        }
                    }
                    if (isset($mailSettings['MAIL_ENCRYPTION'])) config(['mail.mailers.smtp.encryption' => $mailSettings['MAIL_ENCRYPTION']]);
                    if (isset($mailSettings['MAIL_FROM_ADDRESS'])) config(['mail.from.address' => $mailSettings['MAIL_FROM_ADDRESS']]);
                    if (isset($mailSettings['MAIL_FROM_NAME'])) config(['mail.from.name' => $mailSettings['MAIL_FROM_NAME']]);
                }
            }
        } catch (\Exception $e) {
            // Ignore during migrations or when DB is unavailable
        }
    }
}
