<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $prod_id
 * @property string $code
 * @property string $name
 * @property string $category_id
 * @property array|null $colors
 * @property string|null $dimensions
 * @property float $price
 * @property string|null $price_display
 * @property string|null $description
 * @property string $unit
 * @property bool $is_poa
 * @property bool $is_override
 * @property string|null $original_catalog_id
 * @property bool $active
 * @property string|null $created_by
 * @property string $created_at
 * @property string|null $updated_at
 */
class AdminProduct extends Model
{
    public $timestamps = false;

    protected $table = 'admin_products';

    protected $fillable = [
        'prod_id',
        'code',
        'name',
        'category_id',
        'colors',
        'dimensions',
        'price',
        'price_display',
        'description',
        'unit',
        'is_poa',
        'is_override',
        'original_catalog_id',
        'active',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'colors' => 'array',
        'is_poa' => 'boolean',
        'is_override' => 'boolean',
        'active' => 'boolean',
        'price' => 'float',
    ];

    /**
     * Legacy catalog shape used by views and merge logic.
     *
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
    {
        $product = $this->attributesToArray();
        $product['id'] = $this->prod_id;

        if (is_string($product['colors'] ?? null)) {
            $product['colors'] = json_decode($product['colors'], true);
        }

        return $product;
    }
}
