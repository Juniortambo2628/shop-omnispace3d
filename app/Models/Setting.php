<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    public $timestamps = false;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];
}
