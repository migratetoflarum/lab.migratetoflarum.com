<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\IgnoreCheck;
use App\Jobs\ScanTaskManager;
use App\OptOutCheck;
use App\Resources\ScanResource;
use App\Scan;
use App\Website;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ScanController extends Controller
{
    use NormalizeUrls;

    public function show(string $uid)
    {
        /**
         * @var $scan Scan
         */
        $scan = Scan::query()->where('uid', $uid)->firstOrFail();
        $scan->load('website');

        return new ScanResource($scan);
    }

    public function store(Request $request)
    {
        $destination = null;

        if ($request->has('website_id')) {
            $website = Website::query()
                ->where('uid', $request->get('website_id'))
                ->firstOrFail();

            $wantsNewScan = true;
        } else {
            $this->validate($request, [
                'url' => 'required|url',
            ]);

            $url = $request->get('url');

            // TODO: move to ScanResolveCanonical
            $destination = $this->getDestinationUrl($url);

            $normalized = $this->getNormalizedUrl($destination);

            /**
             * @var $website Website
             */
            $website = Website::query()->firstOrCreate([
                'normalized_url' => $normalized,
            ], [
                'canonical_url' => $destination, // So that if we re-scan a website that never successfully scanned, we still have a url to use
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

            $scan->url = $destination ?: $website->canonical_url;

            $website->scans()->save($scan);

            $manager = new ScanTaskManager($scan);
            $manager->start();

            $canonicalUrl = $website->canonical_url ?: $destination;

            if (is_null($website->ignore) && $canonicalUrl) {
                $parsed = $this->getParsedUrl($canonicalUrl);
                $domain = Arr::get($parsed, 'host');

                $check = new OptOutCheck();
                $check->source = 'scan';
                $check->domain = $domain;
                $check->url = $canonicalUrl;
                $check->canonical_url = $canonicalUrl;
                $check->normalized_url = $website->normalized_url;
                $check->save();

                $this->dispatch(new IgnoreCheck($check));
            }
        }

        $scan->load('website');

        return new ScanResource($scan);
    }
}
