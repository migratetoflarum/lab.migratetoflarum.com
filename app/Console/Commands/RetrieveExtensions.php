<?php

namespace App\Console\Commands;

use App\Extension;
use App\ExtensionVersion;
use App\Locale;
use Carbon\Carbon;
use Composer\Semver\Comparator;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RetrieveExtensions extends Command
{
    protected $signature = 'extensions:retrieve {q?}';
    protected $description = 'Retrieve extensions via Packagist';

    protected Client $client;

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

        if ($this->hasArgument('q')) {
            $url .= '&q=' . urlencode($this->argument('q'));
        }

        while ($url) {
            $this->info("Reading $url");

            $response = $this->client->get($url);
            $data = Utils::jsonDecode($response->getBody()->getContents(), true);

            $packages = Arr::get($data, 'results', []);

            foreach ($packages as $package) {
                $packageName = Arr::get($package, 'name');
                $this->info("Saving $packageName");

                $details = Arr::get(Utils::jsonDecode($this->client->get("/packages/$packageName.json")->getBody()->getContents(), true), 'package', []);

                $versions = Arr::get($details, 'versions', []);

                /**
                 * @var $extension Extension
                 */
                $extension = Extension::query()->firstOrNew([
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

                // Save a first time so the extension id can be used when scanning the versions
                $extension->save();

                $lastVersion = null;

                foreach ($versions as $version) {
                    $versionNumber = Arr::get($version, 'version');

                    $this->info("Saving version $versionNumber");

                    /**
                     * @var $extensionVersion ExtensionVersion
                     */
                    $extensionVersion = ExtensionVersion::query()->firstOrNew([
                        'extension_id' => $extension->id,
                        'version' => $versionNumber,
                    ]);

                    $extensionVersion->packagist = $version;
                    $extensionVersion->version_normalized = Arr::get($version, 'version_normalized');
                    $extensionVersion->packagist_time = Carbon::parse(Arr::get($version, 'time'));
                    $extensionVersion->save();

                    if (
                        !Str::startsWith($versionNumber, 'dev-') &&
                        !Str::endsWith($versionNumber, '-dev')
                    ) {
                        $distUrl = Arr::get($version, 'dist.url');

                        if (
                            $distUrl &&
                            !$extensionVersion->hasMedia('dist')
                        ) {
                            try {
                                if (preg_match('~^https://api\.github\.com/repos/[^/]+/[^/]+/zipball/[0-9a-f]+$~', $distUrl) === 1) {
                                    $extensionVersion
                                        ->addMediaFromGitHubApiUrl($distUrl)
                                        ->toMediaCollection('dist');
                                } else {
                                    $this->warn("Unhandled dist file url $distUrl");
                                }
                            } catch (Exception $exception) {
                                // it's not uncommon to hit 404 errors when packages have been deleted from
                                // GitHub but not Packagist
                                if (
                                    $exception instanceof ClientException &&
                                    $exception->hasResponse() &&
                                    $exception->getResponse()->getStatusCode() === 404
                                ) {
                                    $this->warn("Not Found error downloading dist file url $distUrl");
                                } else {
                                    $this->error($exception->getMessage());

                                    // Report other errors to Sentry, but don't interrupt crawling more packages
                                    report($exception);
                                }
                            }
                        }

                        if (is_null($lastVersion) || Comparator::greaterThan($versionNumber, $lastVersion)) {
                            $lastVersion = $versionNumber;
                        }
                    }
                }

                if ($lastVersion) {
                    $latestVersion = Arr::get($versions, $lastVersion);

                    $extension->title = Arr::get($latestVersion, 'extra.flarum-extension.title');
                    $extension->icon = Arr::get($latestVersion, 'extra.flarum-extension.icon');
                    $extension->last_version = $lastVersion;
                    $extension->last_version_time = Carbon::parse(Arr::get($latestVersion, 'time'));

                    $discussUrl = Arr::get($latestVersion, 'support.forum') ?? Arr::get($latestVersion, 'extra.flagrow.discuss');

                    if (preg_match('~^https://discuss\.flarum\.org/d/[a-z0-9_-]+$~', $discussUrl) === 1) {
                        $extension->discuss_url = $discussUrl;
                    } else {
                        $extension->discuss_url = null;
                    }

                    $localeId = null;

                    if ($localeCode = Arr::get($latestVersion, 'extra.flarum-locale.code')) {
                        /**
                         * @var $locale Locale
                         */
                        $locale = Locale::query()->firstOrCreate([
                            'code' => $localeCode,
                        ], [
                            'localized_name' => Arr::get($latestVersion, 'extra.flarum-locale.title'),
                        ]);

                        $localeId = $locale->id;
                    }

                    $extension->flarum_locale_id = $localeId;

                    $extension->lastVersion()->associate($extension->versions()->where('version', $lastVersion)->first());
                }

                $extension->description = Arr::get($details, 'description');
                $extension->abandoned = Arr::get($details, 'abandoned');
                $extension->repository = Arr::get($details, 'repository');
                $extension->packagist_time = Carbon::parse(Arr::get($details, 'time'));
                $extension->save();
            }

            $url = Arr::get($data, 'next');
        }

        $this->info('Done.');
    }
}
