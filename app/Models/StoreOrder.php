<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id', 'order_number', 'customer_name', 'customer_phone', 'customer_email',
        'shipping_address', 'governorate', 'items', 'subtotal', 'shipping', 'total',
        'payment_method', 'payment_status', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function totalInEgp(): float
    {
        return $this->total / 100;
    }
}
