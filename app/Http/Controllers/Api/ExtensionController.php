<?php

namespace App\Http\Controllers\Api;

use App\Extension;
use App\Http\Controllers\Controller;
use App\Resources\ExtensionResource;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    public function index(Request $request)
    {
        $query = Extension::publiclyVisible();

        switch ($request->get('sort')) {
            case 'id':
                $query->orderBy('flarumid');
                break;
            case '-id':
                $query->orderBy('flarumid', 'desc');
                break;
            case 'update_time':
                $query->orderBy('last_version_time');
                break;
            case '-update_time':
                $query->orderBy('last_version_time', 'desc');
                break;
            case 'time':
                $query->orderBy('packagist_time');
                break;
            case '-time':
            default:
                $query->orderBy('packagist_time', 'desc');
                break;
        }

        $filter = $request->get('filter', []);

        switch (array_get($filter, 'abandoned')) {
            case 'all':
                break;
            case 'yes':
                $query->whereNotNull('abandoned');
                break;
            case 'no':
            default:
                $query->whereNull('abandoned');
                break;
        }

        switch (array_get($filter, 'locale')) {
            case 'all':
                break;
            case 'yes':
                $query->whereNotNull('flarum_locale_id');
                break;
            case 'no':
            default:
                $query->whereNull('flarum_locale_id');
                break;
        }

        $search = trim(array_get($filter, 'q'));

        if ($search) {
            $query->where('package', 'like', '%' . $search . '%');
        }

        /**
         * @var $extensions Paginator
         */
        $extensions = $query->paginate();

        $extensions->appends('sort', $request->get('sort', '-time'));
        $extensions->appends('filter', $request->get('filter', []));

        $extensions->load([
            'lastVersion.translationsProvided.locale',
            'lastVersion.translationsProvided.extensionReceiver',
            'lastVersion.translationsReceived.locale',
            'lastVersion.translationsReceived.extensionVersionProvider.extension',
        ]);

        return ExtensionResource::collection($extensions);
    }
}
