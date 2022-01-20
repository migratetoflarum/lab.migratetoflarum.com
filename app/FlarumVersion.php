<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FlarumVersion
{
    const BETA_7 = "0.1.0-beta.7";
    const BETA_8 = "0.1.0-beta.8";
    const BETA_9 = "0.1.0-beta.9";
    const BETA_10 = "0.1.0-beta.10";
    const BETA_11 = "0.1.0-beta.11";
    const BETA_12 = "0.1.0-beta.12";
    const BETA_13 = "0.1.0-beta.13";
    const BETA_14 = "0.1.0-beta.14";
    const BETA_14_1 = "0.1.0-beta.14.1";
    const BETA_15 = "0.1.0-beta.15";
    const BETA_16 = "0.1.0-beta.16";
    const V1_0_0 = "1.0.0";
    const V1_0_1 = "1.0.1";
    const V1_0_2 = "1.0.2";
    const V1_0_3 = "1.0.3";
    const V1_0_4 = "1.0.4";
    const V1_1_0 = "1.1.0";
    const V1_1_1 = "1.1.1";

    // MD5 hash of the javascript of Flarum core, excluding the sourcemap declaration
    // Obtained through the GetCoreJavascriptHash command
    public array $forumJavascriptHashes = [
        '189296cfbba1fc2ed589ba2f15725905' => self::BETA_8,
        'c4852231b8e6286ce37761a1cfab1297' => self::BETA_9,
        '3616121e8824e77d8676060d103765fb' => self::BETA_10,
        '2b7e30e6ed92d2003b55248fb449ad0e' => self::BETA_11,
        '4dc536d471c24565e50bea6c7ad40519' => self::BETA_12,
        'c8e2250a2e9b2941eff58fd352462c89' => self::BETA_13,
        'aae54426912c20366d4da3ca82547b35' => self::BETA_14,
        '70ff3ba6388c6eb2d207806a3bae3c28' => self::BETA_14_1,
        '7a12f38e148ce80b9697ef91b9a84409' => self::BETA_15,
        '13fdce9de3e8eea11c2f9caf6c3b5725' => self::BETA_16,
        '9bfa334d419f1506dcaf3024ec9dec39' => [self::V1_0_0, self::V1_0_1],
        '210e1cab71dfa71126a6dfbe8f5f6dff' => self::V1_0_2,
        '7fd38d2d84278f0c3aa30ea7d5eab99b' => [self::V1_0_3, self::V1_0_4],
        'c3d9c002b7b0b12c3c967723ac41ec77' => [self::V1_1_0, self::V1_1_1],
    ];

    public array $adminJavascriptHashes = [
        '3ac891548d3b255cbe65a97fc4eba017' => self::BETA_8,
        'a558ecde363e01b1cdd6a76a684eba48' => self::BETA_9,
        'de40f6fa13b1c5158bb617fa70b818dc' => self::BETA_10,
        '45293cf0cea1c78d49e7eed85760ddc7' => self::BETA_11,
        'b5b92a80088ecb561362100d2b79440a' => self::BETA_12,
        'd09a0203b4a55d6e61ed8586f7615c01' => self::BETA_13,
        'c78da55dbdca2042269dbfc84ad1770b' => self::BETA_14,
        'a36756c1c89a4eb2ab9aee33d1f7d72c' => self::BETA_14_1,
        '62a602eba5dc4196f7e0dd29b512471e' => self::BETA_15,
        '4fb89c7fabd9eb27d2d6ce948dc34e5a' => self::BETA_16,
        '32493f26cf9fa701003391bd654a13e8' => [self::V1_0_0, self::V1_0_1],
        'db21719109058a04bc1fbb2bfb5b35a7' => self::V1_0_1,
        '40083f039fe8ec6990aa9fe5095707fa' => [self::V1_0_3, self::V1_0_4],
        '74fc375900d66b7c4f14a4aead2febc2' => [self::V1_1_0, self::V1_1_1],
    ];

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

    public static function isV1OrAbove(array $versions): bool
    {
        return Arr::first($versions, function ($version) {
                return Str::startsWith($version, '1.');
            }) !== null;
    }

    public static function versionsFromJavascriptHash(string $frontend, string $hash): array
    {
        $instance = new self;

        // This method returns an array because we could expect versions to be released with the same hash
        switch ($frontend) {
            case 'forum':
                return Arr::wrap(Arr::get($instance->forumJavascriptHashes, $hash));
            case 'admin':
                return Arr::wrap(Arr::get($instance->adminJavascriptHashes, $hash));
        }

        return [];
    }
}
