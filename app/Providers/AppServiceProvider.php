<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());

        // Manually load Brand module migrations and views
        $this->loadMigrationsFrom(module_path('Brand', 'Database/Migrations'));
        $this->loadViewsFrom(module_path('Brand', 'Resources/views'), 'brand');
    }
}
