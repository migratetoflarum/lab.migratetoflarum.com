<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Not using the parent configuration for now
        //parent::boot();

        Horizon::auth(function ($request) {
            if ($request->has('horizon_token')) {
                // Store the token in the session so it doesn't have to be in the url for every request
                session()->put('horizon_token', $request->get('horizon_token'));
            }

            return session('horizon_token') === config('horizon.access_token');
        });
    }
}
