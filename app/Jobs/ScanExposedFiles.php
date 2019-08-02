<?php

namespace App\Jobs;

use App\FlarumVersion;
use Exception;
use Illuminate\Support\Arr;

class ScanExposedFiles extends TaskJob
{
    protected function handleTask()
    {
        $canonical = $this->siblingTask(ScanResolveCanonical::class);

        $safeFlarumUrl = rtrim(Arr::get($canonical->data, 'canonical_url'), '/');

        $this->log(self::LOG_PUBLIC, 'Listing possible vulnerable paths');

        $tryMaliciousAccess = [
            'vendor' => [
                'vendor/composer/installed.json',
                'vendor/flarum/core/LICENSE',
            ],
            'storage' => [
                // Beta 7 paths likely to exist
                'storage/logs/flarum.log',
                'storage/views/7dc8e518535b1d01db47bee524631424', // app.blade.php
            ],
            'composer' => [
                'composer.json',
                'composer.lock',
            ],
        ];

        if (FlarumVersion::isBeta8OrAbove(Arr::get($canonical->data, 'possibleFlarumVersions'))) {
            $tryMaliciousAccess['storage'] = [
                // Beta 8 paths likely to exist
                'storage/logs/flarum-' . date('Y-m-d') . '.log',
                'storage/cache/77/e1/77e1ba46ee3a2b2d1558d7c5d07c4c0caa46c7bf', // sha1 of flarum.formatter
            ];
        }

        // If there's a public folder, there's a good chance this means a misconfigured root folder
        // So we will check malicious access one level up as well
        // The public folder warning will only be shown if at least one path is vulnerable
        $usingPublicFolder = ends_with($safeFlarumUrl, '/public');
        $publicFolderAndVulnerable = false;

        foreach ($tryMaliciousAccess as $access => $urls) {
            $this->data[$access] = [
                'access' => false,
                'safeUrls' => [],
                'vulnerableUrls' => [],
                'errorUrls' => [],
            ];

            foreach ($urls as $url) {
                $this->log(self::LOG_PUBLIC, "Trying to access $url");

                try {
                    $fullUrl = "$safeFlarumUrl/$url";
                    $response = $this->request($fullUrl, 'HEAD', true);

                    if ($response->getStatusCode() === 200) {
                        $this->data['access'] = true;
                        $this->data['vulnerableUrls'][] = $fullUrl;
                    } else {
                        $this->data['safeUrls'][] = $fullUrl;
                    }
                } catch (Exception $exception) {
                    // Errors are not considered to allow malicious access
                    // But the messages are still saved just in case
                    $this->data['errorUrls'][] = $fullUrl;
                }

                if ($usingPublicFolder) {
                    $this->log(self::LOG_PUBLIC, "Trying to access $url one level higher");

                    try {
                        $fullUrl = substr($safeFlarumUrl, 0, -6 /* length of "public" */) . $url;
                        $response = $this->request($fullUrl, 'HEAD', true);

                        if ($response->getStatusCode() === 200) {
                            $this->data['access'] = true;
                            $this->data['vulnerableUrls'][] = $fullUrl;
                        } else {
                            $this->data['safeUrls'][] = $fullUrl;
                        }
                    } catch (Exception $exception) {
                        $this->data['errorUrls'][] = $fullUrl;
                    }
                }
            }

            $this->saveTaskAndBroadCast();
        }

        if ($publicFolderAndVulnerable) {
            $vulnerabilities[] = 'insecure-public-folder';
        }
    }
}
