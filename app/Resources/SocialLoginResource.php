<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class SocialLoginResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'social-logins',
            'id' => $this->resource->id,
            'attributes' => $this->resource->jsonSerialize(),
        ];
    }
}
