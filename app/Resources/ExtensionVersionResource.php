<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExtensionVersionResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'extension-versions',
            'id' => $this->resource->id,
            'attributes' => $this->resource->jsonSerialize(),
        ];
    }
}
