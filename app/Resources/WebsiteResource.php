<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class WebsiteResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'websites',
            'id' => $this->resource->uid,
            'attributes' => [
                'normalized_url' => $this->resource->normalized_url,
                'canonical_url' => $this->resource->canonical_url,
                'name' => $this->resource->name,
                'is_apex' => $this->resource->is_apex,
            ],
        ];
    }
}
