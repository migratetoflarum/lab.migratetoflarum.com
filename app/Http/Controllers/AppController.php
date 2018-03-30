<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ScanController;
use App\Resources\ScanResource;
use App\Scan;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Csp\AddCspHeaders;

class AppController extends Controller
{
    public function __construct()
    {
        $this->middleware(AddCspHeaders::class);
    }

    protected function appView($preload = [])
    {
        $recentScans = Scan::publiclyVisible()
            ->whereHas('website', function (Builder $query) {
                return $query->publiclyVisible();
            })
            ->latest()
            ->take(config('scanner.show_recent_count'))
            ->get();

        $recentScans->load('website');

        $preload = array_merge(ScanResource::collection($recentScans)->jsonSerialize(), $preload);

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
}
