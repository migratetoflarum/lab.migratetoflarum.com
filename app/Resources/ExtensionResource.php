<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExtensionResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'extensions',
            'id' => $this->resource->package,
            'attributes' => $this->resource->jsonSerialize(),
        ];
    }
}
