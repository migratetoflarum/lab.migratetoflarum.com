<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\SocialLogin;
use App\User;
use DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Two\User as TwoUser;

class SocialiteController extends Controller
{
    /**
     * @var Factory
     */
    protected $socialite;

    public function __construct(Factory $socialite)
    {
        $this->socialite = $socialite;
    }

    protected function validateDriver(string $driver)
    {
        // Drivers included with Socialite
        $drivers = [
            'facebook',
            'twitter',
            'linkedin',
            'google',
            'github',
            'bitbucket',
        ];

        if (!in_array($driver, $drivers)) {
            throw ValidationException::withMessages([
                'socialite' => [
                    'Invalid login driver',
                ],
            ]);
        }

        if (!config("services.$driver.client_id")) {
            throw ValidationException::withMessages([
                'socialite' => [
                    'You currently can\'t use this login driver',
                ],
            ]);
        }
    }

    public function redirect(string $driver)
    {
        $this->validateDriver($driver);

        return $this->socialite->driver($driver)->redirect();
    }

    public function callback(string $driver)
    {
        $this->validateDriver($driver);

        $driverUser = $this->socialite->driver($driver)->user();

        /**
         * @var $socialLogin SocialLogin
         */
        $socialLogin = SocialLogin::socialite($driver, $driverUser)->first();

        if ($socialLogin) {
            $user = $socialLogin->appUser;

            if (auth()->check() && optional($user)->id !== auth()->id()) {
                throw ValidationException::withMessages([
                    'socialite' => [
                        'This social account is already linked to another user',
                    ],
                ]);
            }
        } else if (auth()->check()) {
            auth()->user()->socialLogins()->save(new SocialLogin([
                'driver' => $driver,
                'user' => $driverUser->getId(),
                'token' => $driverUser instanceof TwoUser ? $driverUser->token : null,
            ]));

            return redirect('/account');
        } else {
            if (User::where('email', $driverUser->getEmail())->exists()) {
                throw ValidationException::withMessages([
                    'socialite' => [
                        'User already exists with this email and another provider',
                    ],
                ]);
            }

            DB::beginTransaction();

            /**
             * @var $user User
             */
            $user = User::create([
                'email' => $driverUser->getEmail(),
                'name' => $driverUser->getName(),
            ]);

            $user->socialLogins()->save(new SocialLogin([
                'driver' => $driver,
                'user' => $driverUser->getId(),
                'token' => $driverUser instanceof TwoUser ? $driverUser->token : null,
            ]));

            DB::commit();
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'socialite' => [
                    'Could not find the user to login as',
                ],
            ]);
        }

        auth()->login($user);

        return redirect('/');
    }
}
