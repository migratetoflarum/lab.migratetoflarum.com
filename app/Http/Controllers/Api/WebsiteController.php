<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Resources\WebsiteResource;
use App\Website;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Website::publiclyVisible();

        switch ($request->get('sort')) {
            case 'domain':
                $query->orderBy('normalized_url');
                break;
            case '-domain':
                $query->orderBy('normalized_url', 'desc');
                break;
            case 'last_scan':
                $query->orderBy('last_public_scanned_at');
                break;
            case '-last_scan':
                $query->orderBy('last_public_scanned_at', 'desc');
                break;
            case '-name':
                $query->orderBy('name', 'desc');
                break;
            case 'name':
            default:
                $query->orderBy('name');
                break;
        }

        $filter = $request->get('filter', []);

        $search = trim(array_get($filter, 'q'));

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('normalized_url', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        /**
         * @var $websites Paginator
         */
        $websites = $query->paginate();

        $websites->appends('sort', $request->get('sort', 'name'));
        $websites->appends('filter', $filter);

        return WebsiteResource::collection($websites);
    }
}
