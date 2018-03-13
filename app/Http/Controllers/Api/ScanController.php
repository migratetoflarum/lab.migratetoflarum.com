<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\WebsiteScan;
use App\Resources\ScanResource;
use App\Scan;
use App\ScannerClient;
use App\Website;
use Carbon\Carbon;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScanController extends Controller
{
    public function show(string $uid)
    {
        /**
         * @var $scan Scan
         */
        $scan = Scan::where('uid', $uid)->firstOrFail();
        $scan->load('website');

        return new ScanResource($scan);
    }

    public function store(Request $request)
    {
        if ($request->has('website_id')) {
            $website = Website::where('uid', $request->get('website_id'))->firstOrFail();

            $wantsNewScan = true;
        } else {
            $this->validate($request, [
                'url' => 'required|url',
            ]);

            $url = $request->get('url');

            $destination = $this->getDestinationUrl($url);

            $normalized = $this->getNormalizedUrl($destination);

            /**
             * @var $website Website
             */
            $website = Website::firstOrCreate([
                'normalized_url' => $normalized,
            ]);

            $wantsNewScan = false;
        }

        $lastScan = $website->scans()->orderBy('created_at', 'desc')->first();

        if ($lastScan && $lastScan->created_at->gt(Carbon::now()->subMinutes(config('scanner.website_scan_wait')))) {
            if ($wantsNewScan) {
                throw ValidationException::withMessages([
                    'website_id' => [
                        'Please wait ' . config('scanner.website_scan_wait') . ' minutes between scans',
                    ],
                ]);
            }

            $scan = $lastScan;
        } else {
            $scan = new Scan();

            if ($request->get('hidden')) {
                $scan->hidden = true;
            }

            $website->scans()->save($scan);

            $this->dispatch(new WebsiteScan($scan));
        }

        $scan->load('website');

        return new ScanResource($scan);
    }

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

        if (!array_has($address, 'host')) {
            throw $this->createUrlValidationException('No hostname found in address');
        }

        return $address;
    }

    protected function getDestinationUrl(string $url): string
    {
        $address = $this->getParsedUrl($url);

        $host = array_get($address, 'host');

        if (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw $this->createUrlValidationException('The ip address is not publicly reachable');
        }

        if ($host === 'localhost') {
            throw $this->createUrlValidationException('I\'m afraid I can\'t let you do that, Dave');
        }

        $port = array_get($address, 'port');

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
                    $response = $client->get($destination);
                } catch (TransferException $exception) {
                    report($exception);

                    throw $this->createUrlValidationException("An error occurred while connecting to url $destination");
                }

                switch ($response->getStatusCode()) {
                    case 200:
                        // Exit loop
                        break 2;
                    case 301:
                    case 302:
                        $destination = trim(array_first($response->getHeader('Location')));

                        $this->getValidationFactory()->make([
                            'url' => $destination,
                        ], [
                            'url' => 'required|url',
                        ], [
                            'url' => 'The url redirect is not valid (' . $destination . ')',
                        ])->validate();

                        break;
                    default:
                        throw $this->createUrlValidationException('The url returned the status code ' . $response->getStatusCode() . ' (' . $destination . ')');
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

        $baseAddress = array_get($address, 'host') . array_get($address, 'path', '/');

        $remove = 'www.';

        // remove www from base address
        if (starts_with($baseAddress, $remove)) {
            $baseAddress = substr($baseAddress, strlen($remove));
        }

        return $baseAddress;
    }
}
