<?php

namespace App\Providers;

use App\BattleNetApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BattleNetApi::class, function ($app) {
            return new BattleNetApi(config('bnet'));
        });
    }
}
