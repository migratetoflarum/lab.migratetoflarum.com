<?php

namespace App\Resources;

use App\Extension;
use Composer\Semver\Comparator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/**
 * @property Extension $resource
 */
class ExtensionResource extends JsonResource
{
    public function toArray($request)
    {
        $relationships = [];

        $attributes = $this->resource->jsonSerialize();

        $attributes['language_pack'] = !!$this->resource->flarum_locale_id;

        if ($this->resource->pivot && !is_null($this->resource->pivot->possible_versions)) {
            $possibleVersions = json_decode($this->resource->pivot->possible_versions);

            $attributes['possible_versions'] = $possibleVersions;
            // We know possible_versions are sorted from lowest to highest in the task
            $attributes['update_available'] = Comparator::greaterThan($this->resource->last_version, Arr::last($possibleVersions));
        }

        return [
            'type' => 'extensions',
            'id' => $this->resource->package,
            'attributes' => $attributes,
            'relationships' => $relationships,
        ];
    }
}
