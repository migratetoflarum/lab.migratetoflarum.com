<?php

namespace App\Resources;

use App\Report\ReportFormatter;
use App\Scan;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Scan $resource
 */
class ScanResource extends JsonResource
{
    public function toArray($request): array
    {
        $report = new ReportFormatter($this->resource->report);

        return [
            'type' => 'scans',
            'id' => $this->resource->uid,
            'attributes' => [
                'hidden' => $this->resource->hidden,
                'report' => $report->toArray(),
                'scanned_at' => optional($this->resource->scanned_at)->toW3cString(),
                'rating' => $this->resource->rating,
            ],
            'relationships' => [
                'website' => [
                    'data' => new WebsiteResource($this->resource->website),
                ],
                'tasks' => [
                    'data' => TaskResource::collection($this->resource->tasks),
                ],
                'requests' => [
                    'data' => RequestResource::collection($this->resource->requests),
                ],
                'extensions' => [
                    'data' => ExtensionResource::collection($this->resource->extensions),
                ],
            ],
        ];
    }
}
