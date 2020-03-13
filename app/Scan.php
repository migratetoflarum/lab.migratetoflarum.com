<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int $website_id
 * @property string $uid
 * @property bool $hidden
 * @property array $report
 * @property string $rating
 * @property string $url
 * @property Carbon $scanned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Website $website
 * @property Collection|Task[] $tasks
 * @property Collection|Request[] $requests
 * @property Collection|Extension[] $extensions
 */
class Scan extends UidModel
{
    protected $casts = [
        'hidden' => 'bool',
        'report' => 'array',
        'scanned_at' => 'datetime',
    ];

    public function website(): Relations\BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function tasks(): Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function requests(): Relations\HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function extensions(): Relations\BelongsToMany
    {
        return $this->belongsToMany(Extension::class)->withPivot('possible_versions');
    }

    public function scopePubliclyVisible(Builder $query)
    {
        $query
            ->where('hidden', false)
            ->whereNotNull('report')
            ->whereNotNull('scanned_at');
    }
}
