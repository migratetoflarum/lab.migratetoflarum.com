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
            'attributes' => $this->resource->jsonSerialize(),
        ];
    }
}
