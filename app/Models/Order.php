<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $event_slug
 * @property string $company_name
 * @property string $contact_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $tax_id
 * @property string $booth_number
 * @property string|null $special_instructions
 * @property string $payment_method
 * @property float $subtotal
 * @property float $vat
 * @property float $total
 * @property string $status
 * @property string|null $payment_reference
 * @property string $created_at
 * @property string $updated_at
 */
class Order extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'event_slug',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'tax_id',
        'booth_number',
        'special_instructions',
        'payment_method',
        'subtotal',
        'vat',
        'total',
        'status',
        'payment_reference',
        'custom_order_id',
        'client_payment_reference',
        'payment_verification_status',
        'payment_verified_at',
        'payment_verified_by',
        'created_at',
        'updated_at',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}
