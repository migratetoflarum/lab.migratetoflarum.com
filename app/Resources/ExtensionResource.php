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

        if ($this->resource->possibleVersions) {
            $relationships['possible_versions'] = [
                'data' => ExtensionVersionResource::collection($this->resource->possibleVersions),
            ];

            $attributes['update_available'] = Comparator::greaterThan($this->resource->last_version, $this->resource->possibleVersions->last()->version);
        }

        return [
            'type' => 'extensions',
            'id' => $this->resource->package,
            'attributes' => $attributes,
            'relationships' => $relationships,
        ];
    }
}
