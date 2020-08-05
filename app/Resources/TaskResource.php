<?php

namespace App\Resources;

use App\Jobs\ScanHomePage;
use App\Task;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property Task $resource
 */
class TaskResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource->data;

        // We know Discuss and nightly will always run dev-master
        if ($this->resource->job === ScanHomePage::class && Arr::exists($data, 'versions') && Str::contains(Arr::get($data, 'assetsBaseUrl'), [
                'https://discuss.flarum.org',
                'https://nightly.flarum.site',
            ])) {
            $data['versions'] = ['dev-master'];
        }

        return [
            'type' => 'tasks',
            'id' => $this->resource->uid,
            'attributes' => [
                'created_at' => $this->resource->created_at->toW3cString(),
                'started_at' => optional($this->resource->started_at)->toW3cString(),
                'completed_at' => optional($this->resource->completed_at)->toW3cString(),
                'failed_at' => optional($this->resource->failed_at)->toW3cString(),
                'job' => str_replace('App\\Jobs\\', '', $this->resource->job),
                'data' => $data,
                'public_log' => $this->resource->public_log,
                'private_log' => $request->get('horizon_token') === config('horizon.access_token') ? $this->resource->private_log : null,
            ],
        ];
    }
}
