<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $version_id
 * @property int $locale_id
 * @property string $namespace
 * @property int $namespace_extension_id
 * @property int $strings_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ExtensionTranslation extends Model
{
    protected $fillable = [
        'version_id',
        'locale_id',
        'namespace',
        'namespace_extension_id',
        'strings_count',
    ];

    protected $visible = [
        'namespace',
        'strings_count',
    ];

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function extensionReceiver()
    {
        return $this->belongsTo(Extension::class, 'namespace_extension_id');
    }

    public function extensionVersionProvider()
    {
        return $this->belongsTo(ExtensionVersion::class, 'version_id');
    }
}
