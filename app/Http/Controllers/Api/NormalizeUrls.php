<?php

namespace App\Http\Controllers\Api;

use App\ScannerClient;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

trait NormalizeUrls
{
    protected function createUrlValidationException(string $message): ValidationException
    {
        return ValidationException::withMessages([
            'url' => [
                $message
            ],
        ]);
    }

    protected function getParsedUrl(string $url): array
    {
        $address = parse_url($url);

        if ($address === false) {
            throw $this->createUrlValidationException('Could not parse address');
        }

        if (!Arr::has($address, 'host')) {
            throw $this->createUrlValidationException('No hostname found in address');
        }

        return $address;
    }

    /**
     * Attempts to clean the url to find the Flarum root/homepage
     * The first step is using a regex to find common cases, then double-check by looking at the page content
     * Because Flarum might be actually installed in a subfolder that matches one of its own url (for example Flarum at domain.tld/tags/)
     * @param string $url Current final url
     * @param StreamInterface $body Body reference from Guzzle
     * @return null|string The new Flarum root url to try or null if the current one looks good
     */
    protected function shouldTryOtherRootUrl(string $url, StreamInterface $body):? string
    {
        $finalPath = (new Uri($url))->getPath();
        $expectedFlarumUrlPath = $finalPath;

        // We check if the url contains some typical Flarum application path and extract the expected Flarum root path
        // No need to check for login-only paths as these will return a 403 and abort the scan anyway
        if (preg_match('~^(.*/)(d/[^/]+(/[0-9]+)?|u/[^/]+|t/[^/]+|tags|following|all|notifications|flags)$~', $finalPath, $matches) === 1) {
            $expectedFlarumUrlPath = $matches[1];
        }

        // In order to not blindly remove those application path (this could be an actual Flarum install in a subfolder !)
        // We check if our predicted Flarum root path is the one used for the stylesheets in the page
        if ($expectedFlarumUrlPath !== $finalPath) {
            $flarumUrl = null;

            $flarumPage = new Crawler($body->getContents());

            $flarumPage->filter('head link[rel="stylesheet"]')->each(function (Crawler $link) use (&$flarumUrl) {
                $href = $link->attr('href');

                if (!$flarumUrl && str_contains($href, '/assets/forum-')) {
                    $flarumUrl = Arr::first(explode('/assets/forum-', $href, 2)) . '/';
                }
            });

            if ($flarumUrl) {
                $actualFlarumUrlPath = (new Uri($flarumUrl))->getPath();

                if ($actualFlarumUrlPath === $expectedFlarumUrlPath) {
                    // We trigger a new loop run with our new expected and verified Flarum root url
                    // in case there would be another redirect
                    return (string)(new Uri($url))
                        ->withPath($actualFlarumUrlPath)
                        // We remove query and fragment as well, as they probably belonged to the application page previously linked
                        ->withQuery('')
                        ->withFragment('');
                }
            }
        }

        return null;
    }

    protected function getDestinationUrl(string $url): string
    {
        $address = $this->getParsedUrl($url);

        $host = Arr::get($address, 'host');

        if (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw $this->createUrlValidationException('The ip address is not publicly reachable');
        }

        if ($host === 'localhost') {
            throw $this->createUrlValidationException('I\'m afraid I can\'t let you do that, Dave');
        }

        $port = Arr::get($address, 'port');

        if ($port && $port !== 80 && $port !== 443) {
            throw $this->createUrlValidationException('We only scan servers answering http on port 80 and https on port 443');
        }

        /**
         * @var $cache Repository
         */
        $cache = app(Repository::class);

        $key = 'destination-url-' . md5($url);

        $destination = $cache->get($key);

        if (!$destination) {
            /**
             * @var $client ScannerClient
             */
            $client = app(ScannerClient::class);

            $destination = $url;

            $maxRedirects = 5;

            for ($i = 0; $i < $maxRedirects; $i++) {
                try {
                    $response = $client->get($destination, [
                        // We use a different, shorter timeout here
                        // No need to wait for ages if the website entered does not exist
                        'connect_timeout' => config('scanner.normalization_connect_timeout'),
                    ]);
                } catch (TransferException $exception) {
                    throw $this->createUrlValidationException("An error occurred while connecting to url $destination");
                }

                switch ($response->getStatusCode()) {
                    case 200:
                        $other = $this->shouldTryOtherRootUrl($destination, $response->getBody());

                        if ($other) {
                            $destination = $other;

                            continue 2;
                        }

                        // Exit loop
                        break 2;
                    case 301:
                    case 302:
                        $destination = trim(Arr::first($response->getHeader('Location')));

                        $this->getValidationFactory()->make([
                            'url' => $destination,
                        ], [
                            'url' => 'required|url',
                        ], [
                            'url' => 'The url redirect is not valid (' . $destination . ')',
                        ])->validate();

                        break;
                    default:
                        $status = $response->getStatusCode();
                        $text = Arr::get(Response::$statusTexts, $status, 'Unknown status code');

                        throw $this->createUrlValidationException("The url $destination returned the status code $status ($text)");
                }
            }

            if ($response->getStatusCode() !== 200) {
                throw $this->createUrlValidationException('Too many redirects');
            }

            $cache->set($key, $destination, 60 * 60 * 24);
        }

        return $destination;
    }

    protected function getNormalizedUrl(string $url): string
    {
        $address = $this->getParsedUrl($url);

        $baseAddress = Arr::get($address, 'host') . Arr::get($address, 'path', '/');

        $remove = 'www.';

        // remove www from base address
        if (Str::startsWith($baseAddress, $remove)) {
            $baseAddress = substr($baseAddress, strlen($remove));
        }

        return $baseAddress;
    }
}
