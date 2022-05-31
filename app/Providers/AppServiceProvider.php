<?php

namespace App\Providers;

use App\GeoIPDatabase;
use App\ScannerClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Pdp\Rules;

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

        $this->app->singleton(Rules::class, function () {
            $content = Storage::get('public_suffix_list');

            if (empty($content)) {
                throw new \Exception('Public Suffix List cache not available');
            }

            return Rules::fromString($content);
        });

        $this->app->singleton(GeoIPDatabase::class);
    }
}
