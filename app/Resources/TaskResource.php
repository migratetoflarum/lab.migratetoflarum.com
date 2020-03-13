<?php

namespace App\Resources;

use App\Task;
use Illuminate\Http\Resources\Json\Resource;

/**
 * @property Task $resource
 */
class TaskResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'tasks',
            'id' => $this->resource->uid,
            'attributes' => [
                'created_at' => $this->resource->created_at->toW3cString(),
                'started_at' => optional($this->resource->started_at)->toW3cString(),
                'completed_at' => optional($this->resource->completed_at)->toW3cString(),
                'failed_at' => optional($this->resource->failed_at)->toW3cString(),
                'job' => str_replace('App\\Jobs\\', '', $this->resource->job),
                'data' => $this->resource->data,
                'public_log' => $this->resource->public_log,
                'private_log' => $request->get('horizon_token') === config('horizon.access_token') ? $this->resource->private_log : null,
            ],
        ];
    }
}
