<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Resources\WebsiteResource;
use App\Website;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
            case 'name':
                $query->orderBy('name');
                break;
            case '-name':
                $query->orderBy('name', 'desc');
                break;
            case 'user_count':
                $query->orderBy('showcase_meta->userCount');
                break;
            case '-user_count':
                $query->orderBy('showcase_meta->userCount', 'desc');
                break;
            case 'discussion_count':
                $query->orderBy('showcase_meta->discussionCount');
                break;
            case '-discussion_count':
            default:
                $query->orderBy('showcase_meta->discussionCount', 'desc');
                break;
        }

        $filter = $request->get('filter', []);

        $search = trim(Arr::get($filter, 'q'));

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('normalized_url', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('showcase_meta->description', 'like', '%' . $search . '%');
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
