<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExtensionVersionResource extends Resource
{
    public function toArray($request)
    {
        $relationships = [];

        if ($this->resource->relationLoaded('extension')) {
            $relationships['extension'] = [
                'data' => new ExtensionResource($this->resource->extension),
            ];
        }

        if ($this->resource->relationLoaded('translationsProvided')) {
            $relationships['translations_provided'] = [
                'data' => ExtensionTranslationResource::collection($this->resource->translationsProvided),
            ];
        }

        if ($this->resource->relationLoaded('translationsReceived')) {
            $relationships['translations_received'] = [
                'data' => ExtensionTranslationResource::collection($this->resource->translationsReceived),
            ];
        }

        return [
            'type' => 'extension-versions',
            'id' => $this->resource->id,
            'attributes' => $this->resource->jsonSerialize(),
            'relationships' => $relationships,
        ];
    }
}
