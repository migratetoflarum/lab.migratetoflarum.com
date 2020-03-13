<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * @property int $id
 * @property int $extension_id
 * @property string $version
 * @property string $version_normalized
 * @property array $packagist
 * @property array $locale_errors
 * @property Carbon $packagist_time
 * @property Carbon $scanned_modules_at
 * @property Carbon $scanned_locales_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Extension $extension
 * @property Collection|JavascriptModule[] $modules
 */
class ExtensionVersion extends Model implements HasMedia
{
    use AddMediaFromGitHubApiUrl;
    use HasMediaTrait;

    protected $casts = [
        'packagist' => 'array',
        'locale_errors' => 'array',
        'packagist_time' => 'timestamp',
        'scanned_modules_at' => 'timestamp',
        'scanned_locales_at' => 'timestamp',
    ];

    protected $fillable = [
        'extension_id',
        'version',
    ];

    protected $visible = [
        'version',
        'locale_errors',
        'packagist_time',
    ];

    public function extension()
    {
        return $this->belongsTo(Extension::class);
    }

    public function modules()
    {
        return $this
            ->belongsToMany(JavascriptModule::class, 'extension_version_module', 'version_id', 'module_id')
            ->withPivot('checksum');
    }

    public function registerMediaCollections()
    {
        $this
            ->addMediaCollection('dist')
            ->singleFile();
    }
}
