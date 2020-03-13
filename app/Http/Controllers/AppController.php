<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ScanController;
use App\Resources\ScanResource;
use App\Website;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Csp\AddCspHeaders;

class AppController extends Controller
{
    public function __construct()
    {
        $this->middleware(AddCspHeaders::class);
    }

    protected function appView($preload = [])
    {
        /**
         * @var $recentWebsites Collection|Website[]
         */
        $recentWebsites = Website::publiclyVisible()
            ->orderBy('last_public_scanned_at', 'desc')
            ->take(config('scanner.show_recent_count'))
            ->get();

        $recentWebsites->load('lastPubliclyVisibleScan');

        $recentScans = new Collection($recentWebsites->pluck('lastPubliclyVisibleScan'));
        $recentScans->load([
            'website',
            'tasks',
            'requests',
            'extensions',
        ]);

        $bestWebsitesSerialized = cache()->remember('best-websites-preload', config('scanner.best_scans_cache'), function () {
            /**
             * @var $bestWebsites Collection|Website[]
             */
            $bestWebsites = Website::publiclyVisible()
                ->where('last_rating', 'like', 'A%')
                ->orderBy('last_public_scanned_at', 'desc')
                ->take(config('scanner.show_best_count'))
                ->get();

            $bestWebsites->load('lastPubliclyVisibleScan');

            $bestScans = new Collection($bestWebsites->pluck('lastPubliclyVisibleScan'));
            $bestScans->load([
                'website',
                'tasks',
                'requests',
                'extensions',
            ]);

            return ScanResource::collection($bestScans)->jsonSerialize();
        });

        $preload = array_merge(
            ScanResource::collection($recentScans)->jsonSerialize(),
            $bestWebsitesSerialized,
            $preload
        );

        return view('app')->withPreload($preload);
    }

    public function home()
    {
        return $this->appView();
    }

    public function scan(string $id)
    {
        $preload = [(new ScanController())->show($id)];

        return $this->appView($preload);
    }

    public function showcase()
    {
        return view('showcase');
    }
}
