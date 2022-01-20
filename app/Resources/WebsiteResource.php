<?php

namespace App\Resources;

use App\Website;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Website $resource
 */
class WebsiteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type' => 'websites',
            'id' => $this->resource->uid,
            'attributes' => [
                'normalized_url' => $this->resource->normalized_url,
                'canonical_url' => $this->resource->canonical_url,
                'name' => $this->resource->name,
                'is_apex' => $this->resource->is_apex,
                'ignore' => $this->resource->ignore,
                'showcase_meta' => $this->resource->showcase_meta,
                'screenshot_url' => $this->resource->getFirstMediaUrl(Website::COLLECTION_SCREENSHOT),
            ],
        ];
    }
}
