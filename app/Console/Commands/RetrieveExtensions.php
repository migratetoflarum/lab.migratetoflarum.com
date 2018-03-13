<?php

namespace App\Console\Commands;

use App\Extension;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class RetrieveExtensions extends Command
{
    protected $signature = 'extensions:retrieve';
    protected $description = 'Retrieve extensions via Packagist';

    protected $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => 'https://packagist.org',
        ]);
    }

    public function handle()
    {
        $url = '/search.json?type=flarum-extension';

        while ($url) {
            $this->info("Reading $url");

            $response = $this->client->get($url);
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $packages = array_get($data, 'results', []);

            foreach ($packages as $package) {
                $packageName = array_get($package, 'name');
                $this->info("Saving $packageName");

                $details = array_get(\GuzzleHttp\json_decode($this->client->get("/packages/$packageName.json")->getBody()->getContents(), true), 'package', []);

                // For the latest version we simply take the first non-dev version available
                $latestVersion = array_first(array_get($details, 'versions', []), function ($value, $key) {
                    return !starts_with($key, 'dev-');
                });

                /**
                 * @var $extension Extension
                 */
                $extension = Extension::firstOrNew([
                    'package' => $packageName,
                ]);

                $extension->flarumid = str_replace([
                    'flarum-ext-',
                    'flarum-',
                    '/',
                ], [
                    '',
                    '',
                    '-',
                ], $packageName);

                if ($latestVersion) {
                    $extension->title = array_get($latestVersion, 'extra.flarum-extension.title');
                    $extension->icon = array_get($latestVersion, 'extra.flarum-extension.icon');
                }

                $extension->description = array_get($details, 'description');
                $extension->abandoned = array_get($details, 'abandoned');
                $extension->repository = array_get($details, 'repository');
                $extension->save();
            }

            $url = array_get($data, 'next');
        }

        $this->info('Done.');
    }
}
