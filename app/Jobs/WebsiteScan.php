<?php

namespace App\Jobs;

use App\Events\ScanUpdated;
use App\Scan;
use App\ScannerClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class WebsiteScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;
    protected $report;
    protected $responses;

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    public function handle()
    {
        $this->report = [];
        $this->responses = [];

        $baseAddress = $this->scan->website->normalized_url;

        $this->report['base_address'] = $baseAddress;

        $workingUrls = [];

        $this->report['urls'] = [];

        // Try the various urls that might be used to access the forum
        foreach (['', 'www.'] as $prefix) {
            foreach (['https', 'http'] as $scheme) {
                $url = "$scheme://$prefix$baseAddress";

                $urlReport = [];

                try {
                    $response = $this->doRequest($url);

                    $urlReport['status'] = $response->getStatusCode();
                    $urlReport['headers'] = array_only($response->getHeaders(), [
                        'Date',
                        'Content-Security-Policy',
                        'Content-Security-Policy-Report-Only',
                        'Strict-Transport-Security',
                        'Server',
                    ]);

                    switch ($response->getStatusCode()) {
                        case 301:
                        case 302:
                            $urlReport['type'] = 'redirect';
                            $urlReport['redirect_to'] = array_first($response->getHeader('Location'));

                            break;
                        case 200:
                            $urlReport['type'] = 'ok';
                            $workingUrls[] = $url;

                            break;
                        default:
                            $urlReport['type'] = 'httperror';
                    }
                } catch (Exception $exception) {
                    $urlReport['type'] = 'error';
                    $urlReport['exception_class'] = get_class($exception);
                    $urlReport['exception_message'] = $exception->getMessage();
                }

                $this->report['urls'][($prefix === 'www.' ? 'www' : 'apex') . '-' . $scheme] = $urlReport;
            }
        }

        $this->report['multiple_urls'] = count($workingUrls) > 1;

        $canonicalUrl = array_first($workingUrls);
        $this->report['canonical_url'] = $canonicalUrl;

        if ($canonicalUrl) {
            $homepage = new Crawler($this->doRequest($canonicalUrl)->getBody()->getContents());

            $homepageReport = [];

            $flarumUrl = null;

            $homepage->filter('head link[rel="stylesheet"]')->each(function (Crawler $link) use (&$flarumUrl) {
                $href = $link->attr('href');

                if (!$flarumUrl && str_contains($href, '/assets/forum-')) {
                    $flarumUrl = array_first(explode('/assets/forum-', $href, 2));
                }
            });

            $homepageReport['flarum_url'] = $flarumUrl;

            $modules = null;
            $boot = null;

            $homepage->filter('body script')->each(function (Crawler $script) use (&$modules, &$boot) {
                $content = $script->text();

                if (!str_contains($content, 'app.boot')) {
                    return;
                }

                $matches = [];
                if (preg_match('~var modules = (\[[^\n]+\])~', $content, $matches) === 1) {
                    $readModules = json_decode($matches[1]);

                    $modules = [];

                    if (is_array($readModules)) {
                        foreach ($readModules as $module) {
                            if (is_string($module)) {
                                $modules[] = $module;
                            }
                        }
                    }
                }

                $matches = [];
                if (preg_match('~app\.boot\(([^\n]+)\)~', $content, $matches) === 1) {
                    $boot = json_decode($matches[1], true);

                    if (is_array($boot)) {
                        foreach (array_get($boot, 'resources', []) as $resource) {
                            if (array_get($resource, 'type') === 'forums') {
                                $boot = [
                                    'base_url' => array_get($resource, 'attributes.baseUrl'),
                                    'base_path' => array_get($resource, 'attributes.basePath'),
                                    'debug' => array_get($resource, 'attributes.debug'),
                                    'title' => array_get($resource, 'attributes.title'),
                                ];

                                break;
                            }
                        }
                    }
                }
            });


            $homepageReport['modules'] = $modules;
            $homepageReport['boot'] = $boot;

            $maliciousAccess = [];

            if ($flarumUrl) {
                try {
                    $response = $this->doRequest("$flarumUrl/vendor/composer/installed.json");
                    $maliciousAccess['vendor'] = $response->getStatusCode() === 200;

                    $response = $this->doRequest("$flarumUrl/storage/logs/flarum.log");
                    $maliciousAccess['storage'] = $response->getStatusCode() === 200;
                } catch (Exception $e) {
                    throw $e;
                }
            }

            $this->report['homepage'] = $homepageReport;
            $this->report['malicious_access'] = $maliciousAccess;
        }

        $this->scan->report = $this->report;
        $this->scan->scanned_at = Carbon::now();
        $this->scan->save();

        if ($canonicalUrl && $this->scan->website->canonical_url !== $canonicalUrl) {
            $this->scan->website->canonical_url = $canonicalUrl;
        }

        $title = array_get($this->scan->report, 'homepage.boot.title');

        if ($title && $this->scan->website->name !== $title) {
            $this->scan->website->name = $title;
        }

        if ($this->scan->website->isDirty()) {
            $this->scan->website->save();
        }

        event(new ScanUpdated($this->scan));
    }

    protected function doRequest(string $url): ResponseInterface
    {
        if (!array_has($this->responses, $url)) {
            $client = app(ScannerClient::class);

            $this->responses[$url] = $client->get($url);
        }

        return array_get($this->responses, $url);
    }

    public function failed(Exception $exception)
    {
        $this->scan->report = [
            'failed' => true,
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
        ];
        $this->scan->scanned_at = Carbon::now();
        $this->scan->save();

        event(new ScanUpdated($this->scan));
    }
}
