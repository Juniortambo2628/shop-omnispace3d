<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $product_code
 * @property string $product_name
 * @property int|null $stock_limit
 */
class StockLevel extends Model
{
    public $timestamps = false;

    protected $table = 'stock_levels';

    protected $fillable = [
        'product_code',
        'product_name',
        'stock_limit',
    ];
}
