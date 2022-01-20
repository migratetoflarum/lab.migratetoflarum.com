<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $extension_id
 * @property string $version
 * @property string $version_normalized
 * @property array $packagist
 * @property Carbon $packagist_time
 * @property Carbon $scanned_modules_at
 * @property string $javascript_forum_checksum
 * @property string $javascript_admin_checksum
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Extension $extension
 */
class ExtensionVersion extends Model implements HasMedia
{
    use AddMediaFromGitHubApiUrl;
    use InteractsWithMedia;

    protected $casts = [
        'packagist' => 'array',
        'packagist_time' => 'timestamp',
    ];

    protected $fillable = [
        'extension_id',
        'version',
    ];

    protected $visible = [
        'version',
        'packagist_time',
    ];

    public function extension()
    {
        return $this->belongsTo(Extension::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('dist')
            ->singleFile();
    }
}
