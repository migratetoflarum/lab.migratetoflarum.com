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
    public function toArray($request): array
    {
        $relationships = [];

        $attributes = $this->resource->jsonSerialize();

        $attributes['language_pack'] = !!$this->resource->flarum_locale_id;

        if ($this->resource->pivot && !is_null($this->resource->pivot->possible_versions)) {
            $possibleVersions = json_decode($this->resource->pivot->possible_versions);

            $attributes['possible_versions'] = $possibleVersions;
            // We know possible_versions are sorted from lowest to highest in the task
            // We need to trim the "v" in front of version because it creates issue if one of the versions has it and another doesn't
            $attributes['update_available'] = Comparator::greaterThan(trim($this->resource->last_version, 'v'), trim(Arr::last($possibleVersions), 'v'));
        }

        return [
            'type' => 'extensions',
            'id' => $this->resource->package,
            'attributes' => $attributes,
            'relationships' => $relationships,
        ];
    }
}
