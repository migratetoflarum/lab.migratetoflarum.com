<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Resources\WebsiteResource;
use App\Website;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        /**
         * @var $query Builder
         */
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
                // We can't use the "->" shorthand for JSON columns because Laravel
                // wraps it with json_unquote which casts values to string and break sorting
                $query->orderByRaw("json_extract(showcase_meta, '$.userCount')");
                break;
            case '-user_count':
                $query->orderByRaw("json_extract(showcase_meta, '$.userCount') desc");
                break;
            case 'discussion_count':
                $query->orderByRaw("json_extract(showcase_meta, '$.discussionCount')");
                break;
            case '-discussion_count':
            default:
                $query->orderByRaw("json_extract(showcase_meta, '$.discussionCount') desc");
                break;
        }

        $filter = $request->get('filter', []);

        $search = trim(Arr::get($filter, 'q'));

        if ($search) {
            $query->where(function (Builder $query) use ($search) {
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
