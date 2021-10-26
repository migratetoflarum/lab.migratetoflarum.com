<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scan_id
 * @property string $uid
 * @property string $method
 * @property string $url
 * @property Carbon $fetched_at
 * @property int $duration
 * @property array $exception
 * @property array $request_headers
 * @property array $response_headers
 * @property int $response_status_code
 * @property string $response_reason_phrase
 * @property string $response_body
 * @property int $response_body_size
 * @property bool $response_body_truncated
 * @property int $response_body_compressed_size
 * @property array $certificate
 * @property string $ip
 *
 * @property Scan $scan
 */
class Request extends UidModel
{
    public $timestamps = false;

    protected $casts = [
        'fetched_at' => 'datetime',
        'duration' => 'int',
        'exception' => 'array',
        'request_headers' => 'array',
        'response_headers' => 'array',
        'response_status_code' => 'int',
        'response_body_size' => 'int',
        'response_body_truncated' => 'bool',
        'certificate' => 'array',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
