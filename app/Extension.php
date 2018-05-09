<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
 * @property string $last_version
 * @property int $flarum_locale_id
 * @property int $last_version_id
 * @property Carbon $packagist_time
 * @property Carbon $last_version_time
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
        'packagist_time' => 'timestamp',
        'last_version_time' => 'timestamp',
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
        'packagist_time',
        'last_version_time',
    ];

    protected $fillable = [
        'package',
    ];

    public function versions()
    {
        return $this->hasMany(ExtensionVersion::class)->orderBy('version_normalized', 'desc');
    }

    public function lastVersion()
    {
        return $this->belongsTo(ExtensionVersion::class, 'last_version_id');
    }

    public function scopePubliclyVisible(Builder $query)
    {
        $query
            ->where('hidden', false)
            ->whereNotNull('last_version');
    }
}
