<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Pdp\Rules;

/**
 * @property int $id
 * @property string $uid
 * @property string $normalized_url
 * @property string $canonical_url
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection|Scan[] $scans
 * @property bool $is_apex
 */
class Website extends UidModel
{
    protected $fillable = [
        'normalized_url',
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }

    public function lastPubliclyVisibleScan()
    {
        return $this
            ->hasOne(Scan::class)
            ->publiclyVisible()
            ->latest();
    }

    public function scopePubliclyVisible(Builder $query)
    {
        $query
            ->whereNotNull('canonical_url')
            ->whereNotNull('name');
    }

    public function getIsApexAttribute(): bool
    {
        /**
         * @var $rules Rules
         */
        $rules = app(Rules::class);

        $parsedUrl = parse_url("https://{$this->normalized_url}");

        $domain = $rules->resolve(array_get($parsedUrl, 'host'));

        return is_null($domain->getSubDomain());
    }
}
