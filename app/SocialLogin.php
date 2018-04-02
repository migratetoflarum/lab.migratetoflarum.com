<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $driver
 * @property string $user
 * @property string $token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $appUser
 */
class SocialLogin extends Model
{
    protected $fillable = [
        'driver',
        'user',
        'token',
    ];

    protected $visible = [
        'driver',
        'user',
        'created_at',
    ];

    public function scopeSocialite(Builder $query, string $driver, \Laravel\Socialite\Contracts\User $user)
    {
        $query
            ->where('driver', $driver)
            ->where('user', $user->getId());
    }

    public function appUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
