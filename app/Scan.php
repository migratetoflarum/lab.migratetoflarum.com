<?php

namespace App;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $website_id
 * @property string $uid
 * @property bool $hidden
 * @property array $report
 * @property Carbon $scanned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Website $website
 */
class Scan extends UidModel
{
    protected $casts = [
        'hidden' => 'bool',
        'report' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
