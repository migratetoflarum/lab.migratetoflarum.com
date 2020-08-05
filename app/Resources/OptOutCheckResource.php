<?php

namespace App\Resources;

use App\OptOutCheck;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property OptOutCheck $resource
 */
class OptOutCheckResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'opt-out-checks',
            'id' => $this->resource->uid,
            'attributes' => [
                'normalized_url' => $this->resource->normalized_url,
                'ignore' => $this->resource->ignore,
                'checked_at' => optional($this->resource->checked_at)->toW3cString(),
                'created_at' => $this->resource->created_at->toW3cString(),
            ],
        ];
    }
}
