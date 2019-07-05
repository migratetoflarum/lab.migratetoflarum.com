<?php

namespace App;

class FlarumVersion
{
    const BETA_7 = "0.1.0-beta.7";
    const BETA_8 = "0.1.0-beta.8";
    const BETA_9 = "0.1.0-beta.9";

    public static function isBeta7(array $versions)
    {
        return in_array(self::BETA_7, $versions);
    }

    public static function isBeta8OrAbove(array $versions)
    {
        return in_array(self::BETA_8, $versions) || in_array(self::BETA_9, $versions);
    }
}
