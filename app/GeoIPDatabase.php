<?php

namespace App;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use MaxMind\Db\Reader;

class GeoIPDatabase
{
    protected array $reader = [];
    protected Repository $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    protected function reader($database): ?Reader
    {
        if (!array_key_exists($database, $this->reader)) {
            $path = storage_path('app/' . $database);

            if (file_exists($path)) {
                $this->reader[$database] = new Reader($path);
            } else {
                // We cache null so we don't try loading it again
                $this->reader[$database] = null;
            }
        }

        return $this->reader[$database];
    }

    public function getOrganization(string $ip): ?string
    {
        return $this->cache->remember('geoip-org.' . $ip, 3600, function () use ($ip) {
            $reader = $this->reader('dbip-asn-lite.mmdb');

            if (!$reader) {
                return null;
            }

            $meta = $reader->get($ip);

            if ($meta) {
                return Arr::get($meta, 'autonomous_system_organization');
            }

            return null;
        });
    }

    public function getCountryCode(string $ip): ?string
    {
        return $this->cache->remember('geoip-country.' . $ip, 3600, function () use ($ip) {
            $reader = $this->reader('dbip-country-lite.mmdb');

            if (!$reader) {
                return null;
            }

            $meta = $reader->get($ip);

            if ($meta) {
                return Arr::get($meta, 'country.iso_code');
            }

            return null;
        });
    }
}
