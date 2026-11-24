<?php

namespace App\Providers;

use App\Services\AiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AiClient takes scalars (key, model, timeout), so it cannot be
        // auto-wired from the container — build it from config instead.
        $this->app->singleton(AiClient::class, fn () => AiClient::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
