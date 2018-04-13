<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $package
 * @property string $flarumid
 * @property string $title
 * @property string $description
 * @property string $abandoned
 * @property string $repository
 * @property string $discuss_url
 * @property array $icon
 * @property bool $hidden
 * @property bool $last_version
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection|ExtensionVersion[] $versions
 */
class Extension extends Model
{
    protected $casts = [
        'icon' => 'array',
        'hidden' => 'boolean',
    ];

    protected $visible = [
        'package',
        'flarumid',
        'title',
        'description',
        'repository',
        'discuss_url',
        'abandoned',
        'icon',
        'last_version',
    ];

    protected $fillable = [
        'package',
    ];

    public function versions()
    {
        return $this->hasMany(ExtensionVersion::class);
    }
}
