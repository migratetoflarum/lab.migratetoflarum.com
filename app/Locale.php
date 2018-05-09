<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $localized_name
 * @property string $english_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Locale extends Model
{
    protected $fillable = [
        'code',
        'localized_name',
        'english_name',
    ];
}
