<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    protected $fillable = [
        'payment_id',
        'billing_product_id',
        'description',
        'unit_amount',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function billingProduct(): BelongsTo
    {
        return $this->belongsTo(BillingProduct::class);
    }
}
