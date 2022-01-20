<?php

namespace App\Resources;

use App\GeoIPDatabase;
use App\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Request $resource
 */
class RequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type' => 'requests',
            'id' => $this->resource->uid,
            'attributes' => [
                    'method' => $this->resource->method,
                    'url' => $this->resource->url,
                    'fetched_at' => $this->resource->fetched_at->toW3cString(),
                    'duration' => $this->resource->duration,
                    'exception' => $this->resource->exception,
                    'request_headers' => $this->resource->request_headers,
                    'response_headers' => $this->resource->response_headers,
                    'response_status_code' => $this->resource->response_status_code,
                    'response_reason_phrase' => $this->resource->response_reason_phrase,
                    'response_body' => $this->resource->response_body,
                    'response_body_size' => $this->resource->response_body_size,
                    'response_body_compressed_size' => $this->resource->response_body_compressed_size,
                    'certificate' => $this->resource->certificate,
                ] + $this->ipInfo(),
        ];
    }

    protected function ipInfo(): array
    {
        if (!$this->resource->ip) {
            return [];
        }

        $info = [
            'ip' => $this->resource->ip,
        ];

        /**
         * @var GeoIPDatabase $database
         */
        $database = app(GeoIPDatabase::class);

        $org = $database->getOrganization($this->resource->ip);

        if ($org) {
            $info['ipOrg'] = $org;
        }

        $country = $database->getCountryCode($this->resource->ip);

        if ($country) {
            $info['ipCountry'] = $country;
        }

        return $info;
    }
}
