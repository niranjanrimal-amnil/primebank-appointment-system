<?php

namespace AppointmentSystem;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppointmentServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/Config/appointment.php', 'appointment'
        );

        // Register services
        $this->app->singleton('appointment.external-api', function ($app) {
            return new Services\ExternalApiService();
        });
    }

    public function boot()
    {
        // Load routes
        // FIX #1: Remove ../ from this line
        Route::prefix('api')
         ->group(function () {
             $this->loadRoutesFrom(__DIR__.'/routes/api.php');
         });
        
        // Load migrations
        // FIX #2: Remove ../ from this line
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        
        // Publish config
        $this->publishes([
            __DIR__.'/Config/appointment.php' => config_path('appointment.php'),
        ], 'appointment-config');
    }
}