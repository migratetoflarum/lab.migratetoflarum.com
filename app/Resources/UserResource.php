<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
{
    public function toArray($request)
    {
        return [
            'type' => 'users',
            'id' => $this->resource->id,
            'attributes' => $this->resource->jsonSerialize(),
            'relationships' => [
                'socialLogins' => [
                    'data' => SocialLoginResource::collection($this->resource->socialLogins),
                ],
            ],
        ];
    }
}
