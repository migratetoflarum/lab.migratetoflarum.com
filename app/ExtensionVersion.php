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
 * @property array $packagist
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
    ];

    protected $fillable = [
        'extension_id',
        'version',
    ];

    protected $visible = [
        'version',
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
