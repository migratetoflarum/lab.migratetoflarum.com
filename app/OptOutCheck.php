<?php

namespace App;

use Carbon\Carbon;

/**
 * @property int $id
 * @property string $uid
 * @property string $source
 * @property string $domain
 * @property string $url
 * @property string $normalized_url
 * @property string $canonical_url
 * @property boolean $ignore
 * @property Carbon $checked_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OptOutCheck extends UidModel
{
    protected $casts = [
        'ignore' => 'boolean',
        'checked_at' => 'datetime',
    ];
}
