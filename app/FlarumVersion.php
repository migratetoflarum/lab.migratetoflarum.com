<?php

namespace App;

use Illuminate\Support\Arr;

class FlarumVersion
{
    const BETA_7 = "0.1.0-beta.7";
    const BETA_8 = "0.1.0-beta.8";
    const BETA_9 = "0.1.0-beta.9";
    const BETA_10 = "0.1.0-beta.10";
    const BETA_11 = "0.1.0-beta.11";
    const BETA_12 = "0.1.0-beta.12";

    public static function isBeta7(array $versions): bool
    {
        return in_array(self::BETA_7, $versions);
    }

    public static function isBeta8OrAbove(array $versions): bool
    {
        return Arr::first($versions, function ($version) {
                return $version !== self::BETA_7;
            }) !== null;
    }
}
