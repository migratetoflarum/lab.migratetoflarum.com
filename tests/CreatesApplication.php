<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;
use Pdp\Rules;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        Hash::driver('bcrypt')->setRounds(4);

        // Use a simple list of rules as the full list can't be pulled from the cache in the test application
        $app->singleton(Rules::class, function () {
            return new Rules([
                Rules::ICANN_DOMAINS => [
                    'com' => [],
                    'uk' => [
                        'co' => [],
                    ],
                ],
            ]);
        });

        return $app;
    }
}
