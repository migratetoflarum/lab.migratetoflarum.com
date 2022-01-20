<?php

namespace App\Providers;

use App\GeoIPDatabase;
use App\ScannerClient;
use Illuminate\Support\ServiceProvider;
use Pdp\Rules;
use Storage;

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
        $this->app->bind(ScannerClient::class, function () {
            return new ScannerClient([
                'allow_redirects' => false,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => config('scanner.client.user_agent'),
                    'Accept-Encoding' => config('scanner.client.accept_encoding'),
                ],
                'connect_timeout' => config('scanner.client.connect_timeout'),
                'timeout' => config('scanner.client.timeout'),
            ]);
        });

        $this->app->bind(Rules::class, function () {
            $rules = Storage::get('public_suffix_list_converted');

            return new Rules(unserialize($rules));
        });

        $this->app->singleton(GeoIPDatabase::class);
    }
}
