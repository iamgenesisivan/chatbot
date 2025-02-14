<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OllamaService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OllamaService::class, function ($app) {
            return new OllamaService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
