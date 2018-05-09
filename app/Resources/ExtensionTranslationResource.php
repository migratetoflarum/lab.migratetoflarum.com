<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExtensionTranslationResource extends Resource
{
    public function toArray($request)
    {
        $attributes = $this->resource->jsonSerialize();

        $attributes['locale_code'] = $this->resource->relationLoaded('locale') ? $this->resource->locale->code : null;

        $relationships = [];

        if ($this->resource->relationLoaded('extensionVersionProvider')) {
            $relationships['extension_version_provider'] = [
                'data' => new ExtensionVersionResource($this->resource->extensionVersionProvider),
            ];
        }

        if ($this->resource->relationLoaded('extensionReceiver')) {
            $relationships['extension_receiver'] = [
                'data' => $this->resource->extensionReceiver ? new ExtensionResource($this->resource->extensionReceiver) : null,
            ];
        }

        return [
            'type' => 'extension-translations',
            'id' => $this->resource->id,
            'attributes' => $attributes,
            'relationships' => $relationships,
        ];
    }
}
