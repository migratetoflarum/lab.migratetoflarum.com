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
        $recent = Scan::where('hidden', false)
            ->whereHas('website', function (Builder $query) {
                $query->whereNotNull('canonical_url')
                    ->whereNotNull('name');
            })
            ->orderBy('scanned_at', 'desc')
            ->take(config('scanner.show_recent_count'))
            ->get();

        $recent->load('website');

        $preload = array_merge(ScanResource::collection($recent)->jsonSerialize(), $preload);

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
