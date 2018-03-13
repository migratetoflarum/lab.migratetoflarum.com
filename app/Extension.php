<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $package
 * @property string $flarumid
 * @property string $title
 * @property string $description
 * @property string $abandoned
 * @property string $repository
 * @property array $icon
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Extension extends Model
{
    protected $casts = [
        'icon' => 'array',
    ];

    protected $visible = [
        'package',
        'flarumid',
        'title',
        'description',
        'repository',
        'abandoned',
        'icon',
    ];

    protected $fillable = [
        'package',
    ];
}
