<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $stack
 * @property string $module
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection|ExtensionVersion[] $extensionVersions
 */
class JavascriptModule extends Model
{
    protected $fillable = [
        'stack',
        'module',
    ];

    public function extensionVersions()
    {
        return $this
            ->belongsToMany(ExtensionVersion::class, 'extension_version_module', 'module_id', 'version_id')
            ->withPivot('checksum');
    }
}
