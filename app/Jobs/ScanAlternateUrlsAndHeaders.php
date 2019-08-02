<?php

namespace App\Jobs;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Pdp\Rules;

class ScanAlternateUrlsAndHeaders extends TaskJob
{
    public function isApex(): bool
    {
        /**
         * @var $rules Rules
         */
        $rules = app(Rules::class);

        $parsedUrl = parse_url("https://{$this->normalized_url}");

        $domain = $rules->resolve(array_get($parsedUrl, 'host'));

        return is_null($domain->getSubDomain());
    }

    protected function handleTask()
    {
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

                $urlReport = [];

                try {
                    $response = $this->request($url);

                    $urlReport['status'] = $response->getStatusCode();
                    $urlReport['headers'] = Arr::only($response->getHeaders(), [
                        'Content-Security-Policy',
                        'Content-Security-Policy-Report-Only',
                        'Strict-Transport-Security',
                    ]);

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
                }

                $this->data[($prefix === 'www.' ? 'www' : 'apex') . '-' . $scheme] = $urlReport;
            }
        }

        $this->data['multipleUrls'] = count($workingUrls) > 1;
        $this->data['firstWorkingUrl'] = Arr::first($workingUrls);

        $wwwIsCanonical = Str::startsWith(array_get($scan->report, 'canonical_url'), ['https://www.', 'http://www.']);

        $this->data['wwwShouldWork'] = Arr::exists($this->data, 'www-http') && ($wwwIsCanonical || $this->isApex());
    }
}
