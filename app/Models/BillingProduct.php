<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'price',
        'image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
