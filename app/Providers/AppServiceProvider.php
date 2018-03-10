<?php

namespace App\Providers;

use App\ScannerClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Horizon::auth(function ($request) {
            if ($request->has('horizon_token')) {
                // Store the token in the session so it doesn't have to be in the url for every request
                session()->put('horizon_token', $request->get('horizon_token'));
            }

            return session('horizon_token') === config('horizon.access_token');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ScannerClient::class, function () {
            return new ScannerClient([
                'allow_redirects' => false,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'MigrateToFlarum Lab',
                ],
            ]);
        });
    }
}
