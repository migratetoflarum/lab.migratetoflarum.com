<?php

namespace App\Jobs;

use App\Exceptions\TaskManualFailException;
use App\FlarumVersionGuesser;
use Illuminate\Support\Arr;
use Symfony\Component\DomCrawler\Crawler;

class ScanHomePage extends TaskJob
{
    protected function handleTask()
    {
        $canonical = $this->siblingTask(ScanResolveCanonical::class);

        $bodyContent = $this->request($canonical->getData('destinationUrl'))->getBody()->getContents();
        $homepage = new Crawler($bodyContent);

        $this->log(self::LOG_PUBLIC, 'Reading boot asset url');

        $homepage->filter('head link[rel="stylesheet"]')->each(function (Crawler $link) {
            $href = $link->attr('href');

            if (str_contains($href, '/assets/forum-')) {
                $this->data['assetsBaseUrl'] = Arr::first(explode('/assets/forum-', $href, 2));
            }

            // New Flarum V1 format
            if (str_contains($href, '/assets/forum.css')) {
                $this->data['assetsBaseUrl'] = Arr::first(explode('/assets/forum.css', $href, 2));
            }
        });

        if (!isset($this->data['assetsBaseUrl'])) {
            throw new TaskManualFailException('Could not identify assets base url');
        }

        $this->data['safeFlarumUrl'] = rtrim($this->data['assetsBaseUrl'], '/');

        $this->log(self::LOG_PUBLIC, 'Reading boot script');

        $homepage->filter('body script')->each(function (Crawler $script) use ($bodyContent) {
            $content = $script->text('', false);

            if (!str_contains($content, 'app.boot')) {
                return;
            }

            $versionGuesser = new FlarumVersionGuesser();
            $this->data['versions'] = $versionGuesser->guess($bodyContent, $content);

            $this->log(self::LOG_PUBLIC, 'Reading boot modules');

            $matches = [];
            // Will only detect beta7 modules
            // beta8 registers them inside the external script file
            if (preg_match('~var modules = (\[[^\n]+\])~', $content, $matches) === 1) {
                $readModules = json_decode($matches[1]);

                $this->data['modules'] = [];

                if (is_array($readModules)) {
                    foreach ($readModules as $module) {
                        if (is_string($module)) {
                            $this->data['modules'][] = $module;
                        }
                    }
                }
            }

            $this->log(self::LOG_PUBLIC, 'Reading boot payload');

            $matches = [];
            // beta7 calls app.boot() with the payload
            // beta8 calls app.load() with the payload then app.boot() without arguments
            // The json object ends at the end of the line with a closing ); or it might be inlined with the next line if optimized by proxy
            if (preg_match('~app\.(boot|load)\(([^\n]+)\);?\s*(?:$|flarum\.core\.app\.bootExtensions\(flarum\.extensions\))~', $content, $matches) === 1) {
                $bootArguments = null;

                try {
                    $bootArguments = \GuzzleHttp\json_decode($matches[2], true);
                } catch (\InvalidArgumentException $exception) {
                    $this->log(self::LOG_PRIVATE, $exception->getMessage());
                }

                if (is_array($bootArguments)) {
                    foreach (Arr::get($bootArguments, 'resources', []) as $resource) {
                        $type = Arr::get($resource, 'type');

                        $this->log(self::LOG_PRIVATE, "Parsing JSON:API object of type $type");

                        if ($type === 'forums') {
                            $this->data['bootBaseUrl'] = Arr::get($resource, 'attributes.baseUrl');
                            $this->data['bootBasePath'] = Arr::get($resource, 'attributes.basePath');
                            $this->data['debug'] = Arr::get($resource, 'attributes.debug');
                            $this->data['bootTitle'] = Arr::get($resource, 'attributes.title');

                            break;
                        }
                    }
                } else {
                    $this->log(self::LOG_PUBLIC, 'Found boot payload but format is invalid');
                }
            } else {
                $this->log(self::LOG_PUBLIC, 'Could not find app.boot() call');
            }
        });

        if (!Arr::exists($this->data, 'debug')) {
            throw new TaskManualFailException('Could not read boot payload');
        }
    }
}
