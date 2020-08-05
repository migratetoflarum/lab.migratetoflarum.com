<?php

namespace App\Resources;

use App\ExtensionVersion;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ExtensionVersion $resource
 */
class ExtensionVersionResource extends JsonResource
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
