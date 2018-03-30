<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

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

    public function scopePubliclyVisible(Builder $query)
    {
        $query
            ->where('hidden', false)
            ->whereNotNull('report')
            ->whereNotNull('scanned_at');
    }
}
