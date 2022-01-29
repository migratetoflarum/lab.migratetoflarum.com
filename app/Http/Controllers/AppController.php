<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ScanController;
use App\Resources\ScanResource;
use App\Stats;
use App\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
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
            ->where(function (Builder $builder) {
                $builder->where('last_rating', 'like', 'A%')
                    ->orWhere('last_rating', 'like', 'B%')
                    ->orWhere('last_rating', 'like', 'C%');
            })
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

        $preload = array_merge(
            ScanResource::collection($recentScans)->jsonSerialize(),
            $preload
        );

        $stats = Cache::get('stats', function () {
            return (new Stats())();
        });

        return view('app')->withPreload($preload)->withStats($stats);
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
