<?php

namespace App\Resources;

use App\Extension;
use App\Report\RatingAgent;
use App\Report\ReportFormatter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\Resource;

class ScanResource extends Resource
{
    public function toArray($request)
    {
        $report = new ReportFormatter($this->resource->report);

        /**
         * @var $matchingExtensions Collection
         */
        $matchingExtensions = Extension::whereIn('flarumid', $report->flarumExtensionIds())->orderBy('package')->get();

        $extensions = $matchingExtensions->filter(function (Extension $extension) use ($matchingExtensions) {
            // Keep any extension that isn't abandoned
            if (!$extension->abandoned) {
                return true;
            }

            // If the extension is abandoned but another one matches the flarum id,
            // remove this extension. Occurs when an extension was renamed and therefore there are multiple matches
            // We assume the forum is already using the non-abandoned version
            $duplicateNotAbandoned = $matchingExtensions->first(function (Extension $duplicate) use ($extension) {
                return !$duplicate->abandoned && $duplicate->flarumid === $extension->flarumid;
            });

            return is_null($duplicateNotAbandoned);
        })->values();

        $agent = new RatingAgent($this->resource);
        $agent->rate();

        return [
            'type' => 'scans',
            'id' => $this->resource->uid,
            'attributes' => [
                'hidden' => $this->resource->hidden,
                'report' => $report->toArray(),
                'scanned_at' => optional($this->resource->scanned_at)->toW3cString(),
                'rating' => $agent->rating,
                'rating_rules' => $agent->importantRules,
            ],
            'relationships' => [
                'website' => [
                    'data' => new WebsiteResource($this->resource->website),
                ],
                'extensions' => [
                    'data' => ExtensionResource::collection($extensions),
                ],
            ],
        ];
    }
}
