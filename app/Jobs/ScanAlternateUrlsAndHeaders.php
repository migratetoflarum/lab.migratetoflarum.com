<?php

namespace App\Jobs;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Pdp\Rules;

class ScanAlternateUrlsAndHeaders extends TaskJob
{
    public function isApex(string $baseAddress): bool
    {
        /**
         * @var $rules Rules
         */
        $rules = app(Rules::class);

        $parsedUrl = parse_url("https://$baseAddress");

        $domain = $rules->resolve(Arr::get($parsedUrl, 'host'));

        return is_null($domain->getSubDomain());
    }

    protected function handleTask()
    {
        $canonical = $this->siblingTask(ScanResolveCanonical::class);

        $baseAddress = $canonical->getData('normalizedUrl');

        $workingUrls = [];
        $prefixes = [''];

        // Add protocol just so it can be parsed
        $parsedUrl = parse_url("https://$baseAddress");

        $this->data['isIP'] = filter_var(Arr::get($parsedUrl, 'host'), FILTER_VALIDATE_IP);

        // Do not try to access www. if the host is an ip
        if (!$this->data['isIP']) {
            $prefixes[] = 'www.';
        }

        // Try the various urls that might be used to access the forum
        foreach ($prefixes as $prefix) {
            foreach (['https', 'http'] as $scheme) {
                $url = "$scheme://$prefix$baseAddress";

                $this->log(self::LOG_PUBLIC, "Checking $url");

                $urlReport = [];

                try {
                    $response = $this->request($url);

                    $headers = [];

                    foreach ([
                                 'Content-Security-Policy',
                                 'Content-Security-Policy-Report-Only',
                                 'Strict-Transport-Security',
                             ] as $headerName) {
                        // Use a loop, so we can use getHeader to retrieve the header in a case-insensitive manner
                        // while still naming the key with the "official" case
                        $headers[$headerName] = $response->getHeader($headerName);
                    }

                    $urlReport['status'] = $response->getStatusCode();
                    $urlReport['headers'] = $headers;

                    switch ($response->getStatusCode()) {
                        case 301:
                        case 302:
                            $urlReport['type'] = 'redirect';
                            $urlReport['redirect_to'] = Arr::first($response->getHeader('Location'));

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

                $this->data[($prefix === 'www.' ? 'www' : 'apex') . '-' . $scheme] = $urlReport;
            }
        }

        $this->data['multipleUrls'] = count($workingUrls) > 1;
        $this->data['firstWorkingUrl'] = Arr::first($workingUrls);

        $homepage = $this->siblingTask(ScanHomePage::class);

        $wwwIsCanonical = Str::startsWith($homepage->getData('assetsUrl'), ['https://www.', 'http://www.']);

        $this->data['wwwShouldWork'] = Arr::exists($this->data, 'www-http') && ($wwwIsCanonical || $this->isApex($baseAddress));
    }
}
