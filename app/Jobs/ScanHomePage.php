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

        $homepage = new Crawler($this->request(Arr::get($canonical->data, 'canonicalUrl'))->getBody()->getContents());

        $this->log(self::LOG_PUBLIC, 'Reading boot asset url');

        $homepage->filter('head link[rel="stylesheet"]')->each(function (Crawler $link) {
            $href = $link->attr('href');

            if (!$flarumUrl && str_contains($href, '/assets/forum-')) {
                $this->data['assetsBaseUrl'] = Arr::first(explode('/assets/forum-', $href, 2));
            }
        });

        // TODO: can be allowed to fail
        if (!$this->data['assetsBaseUrl']) {
            throw new TaskManualFailException('Could not identify assets base url');
        }

        $this->log(self::LOG_PUBLIC, 'Reading boot script');

        $homepage->filter('body script')->each(function (Crawler $script) {
            $content = $script->text();

            if (!str_contains($content, 'app.boot')) {
                return;
            }

            $versionGuesser = new FlarumVersionGuesser();
            $this->data['version'] = $versionGuesser->guess($content);

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
            if (preg_match('~app\.(boot|load)\(([^\n]+)\)~', $content, $matches) === 1) {
                $bootArguments = json_decode($matches[2], true);

                if (is_array($bootArguments)) {
                    foreach (Arr::get($bootArguments, 'resources', []) as $resource) {
                        if (Arr::get($resource, 'type') === 'forums') {

                            $this->data['bootBaseUrl'] = Arr::get($resource, 'attributes.baseUrl');
                            $this->data['bootBasePath'] = Arr::get($resource, 'attributes.basePath');
                            $this->data['debug'] = Arr::get($resource, 'attributes.debug');
                            $this->data['bootTitle'] = Arr::get($resource, 'attributes.title');

                            break;
                        }
                    }
                }
            }
        });

        if (!Arr::exists($this->data, 'debug')) {
            throw new TaskManualFailException('Could not read boot payload');
        }

        $this->log(self::LOG_PUBLIC, 'Reading boot asset hash');

        $homepage->filter('body script[src]')->each(function (Crawler $link) use (&$forumJsHash) {
            $src = $link->attr('src');

            if (!$forumJsHash && preg_match('~assets/forum\-([0-9a-f]{8})\.js$~', $src, $matches) === 1) {
                $data['assetsForumJSHash'] = $matches[1];
            }
        });

        if (!$this->data['assetsForumJSHash']) {
            throw new TaskManualFailException('Could not identify JS assets hash');
        }
    }
}
