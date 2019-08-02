<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $task_id
 * @property bool $sensitive
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
 *
 * @property Task $task
 */
class Request extends Model
{
    public $timestamps = false;

    protected $casts = [
        'sensitive' => 'bool',
        'fetched_at' => 'datetime',
        'duration' => 'int',
        'exception' => 'array',
        'request_headers' => 'array',
        'response_headers' => 'array',
        'response_status_code' => 'int',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
