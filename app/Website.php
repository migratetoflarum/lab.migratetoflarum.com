<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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
}
