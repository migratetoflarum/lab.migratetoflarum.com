<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Pdp\Rules;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * @property int $id
 * @property string $uid
 * @property string $normalized_url
 * @property string $canonical_url
 * @property string $name
 * @property string $last_rating
 * @property Carbon $last_public_scanned_at
 * @property boolean $ignore
 * @property array $showcase_meta
 * @property Carbon $pinged_at
 * @property Carbon $confirmed_flarum_at
 * @property boolean $is_flarum
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection|Scan[] $scans
 * @property bool $is_apex
 */
class Website extends UidModel implements HasMedia
{
    use HasMediaTrait;

    const COLLECTION_SCREENSHOT = 'screenshot';

    protected $fillable = [
        'normalized_url',
    ];

    protected $casts = [
        'ignore' => 'boolean',
        'showcase_meta' => 'array',
        'pinged_at' => 'datetime',
        'confirmed_flarum_at' => 'datetime',
        'is_flarum' => 'boolean',
    ];

    // Multiple of 2, 3 and 4 so the pagination looks nicely in grids
    protected $perPage = 24;

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }

    public function lastPubliclyVisibleScan()
    {
        return $this
            ->hasOne(Scan::class)
            ->publiclyVisible()
            ->orderBy('scanned_at', 'desc');
    }

    public function scopePubliclyVisible(Builder $query)
    {
        $query
            // TODO: can be replaced with simply ignore == 0 once most existing websites have been scanned again with an ignore check
            ->where(function ($query) {
                $query
                    ->whereNull('ignore')
                    ->orWhere('ignore', '=', 0);
            })
            ->whereNotNull('canonical_url')
            ->whereNotNull('last_public_scanned_at')
            ->where('is_flarum', '=', 1);
    }

    public function getIsApexAttribute(): bool
    {
        /**
         * @var $rules Rules
         */
        $rules = app(Rules::class);

        $parsedUrl = parse_url("https://{$this->normalized_url}");

        $domain = $rules->resolve(Arr::get($parsedUrl, 'host'));

        return is_null($domain->getSubDomain());
    }

    public function updateIsFlarumStatus(bool $isFlarumNow)
    {
        if ($isFlarumNow) {
            $this->confirmed_flarum_at = now();
            $this->is_flarum = true;
        } else if ($this->is_flarum && $this->confirmed_flarum_at && $this->confirmed_flarum_at->gt(now()->subDays(config('scanner.ping.remove_after')))) {
            // Keep flarum status until the remove period is reached
        } else {
            $this->is_flarum = false;
        }
    }
}
