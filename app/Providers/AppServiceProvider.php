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
        // Pastikan public_path mengarah ke public_html jika di shared hosting cPanel
        $publicHtml = dirname($this->app->basePath()) . '/public_html';
        if (is_dir($publicHtml)) {
            $this->app->usePublicPath($publicHtml);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa HTTPS di production agar gambar dan asset tidak dicegat Mixed Content
        if (
            app()->environment('production') ||
            str_starts_with(config('app.url'), 'https://') ||
            str_contains(request()->getHost(), 'ngrok') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (request()->header('x-forwarded-proto') === 'https')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
