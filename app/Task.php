<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * @property int $id
 * @property int $website_id
 * @property int $scan_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $started_at
 * @property Carbon $completed_at
 * @property Carbon $failed_at
 * @property string $job
 * @property array $data
 * @property string $public_log
 * @property string $private_log
 * @property string $fail_message
 *
 * @property Website $website
 * @property Scan $scan
 */
class Task extends Model
{
    //TODO: casts
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function getData(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }
}
