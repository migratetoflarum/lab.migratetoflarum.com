<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\IgnoreCheck;
use App\OptOutCheck;
use App\Resources\OptOutCheckResource;
use App\Resources\WebsiteResource;
use App\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OptOutController extends Controller
{
    use NormalizeUrls;

    public function check(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $url = $request->get('url');

        $destination = $this->getDestinationUrl($url);

        $normalized = $this->getNormalizedUrl($destination);

        $website = Website::query()->where('normalized_url', $normalized)->first();
        $check = null;

        if (!$website || is_null($website->ignore) || $request->has('check_now')) {
            $parsed = $this->getParsedUrl($url);
            $domain = Arr::get($parsed, 'host');

            /**
             * @var $lastCheck OptOutCheck
             */
            $lastCheck = OptOutCheck::query()->where('domain', $domain)->latest()->first();

            if ($lastCheck && !$lastCheck->checked_at) {
                $check = $lastCheck;
            } else if ($lastCheck && $lastCheck->checked_at->gt(now()->subMinutes(config('scanner.website_opt_out_wait')))) {
                throw $this->createUrlValidationException('Please wait ' . config('scanner.website_opt_out_wait') . ' minutes between opt out checks');
            } else {
                $check = new OptOutCheck();
                $check->source = 'manual';
                $check->domain = $domain;
                $check->url = $url;
                $check->canonical_url = $destination;
                $check->normalized_url = $normalized;
                $check->save();

                $this->dispatch(new IgnoreCheck($check));
            }
        }

        return [
            'website' => $website ? new WebsiteResource($website) : null,
            'check' => $check ? new OptOutCheckResource($check) : null,
        ];
    }
}
