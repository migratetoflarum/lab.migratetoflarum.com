<?php

namespace App\Resources;

use Composer\Semver\Comparator;
use Illuminate\Http\Resources\Json\Resource;

class ExtensionResource extends Resource
{
    public function toArray($request)
    {
        $relationships = [];

        $attributes = $this->resource->jsonSerialize();

        $attributes['language_pack'] = !!$this->resource->flarum_locale_id;

        if ($this->resource->possibleVersions) {
            $relationships['possible_versions'] = [
                'data' => ExtensionVersionResource::collection($this->resource->possibleVersions),
            ];

            $attributes['update_available'] = Comparator::greaterThan($this->resource->last_version, $this->resource->possibleVersions->last()->version);
        }

        if ($this->resource->relationLoaded('versions')) {
            $relationships['versions'] = [
                'data' => ExtensionVersionResource::collection($this->resource->versions),
            ];
        }

        if ($this->resource->relationLoaded('lastVersion')) {
            $relationships['last_version'] = [
                'data' => $this->resource->last_version ? new ExtensionVersionResource($this->resource->lastVersion) : null,
            ];
        }

        return [
            'type' => 'extensions',
            'id' => $this->resource->package,
            'attributes' => $attributes,
            'relationships' => $relationships,
        ];
    }
}
