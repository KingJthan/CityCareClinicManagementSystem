<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'cashier_id',
        'invoice_number',
        'amount',
        'payment_method',
        'status',
        'reference',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_payment_status',
        'online_payment_started_at',
        'online_payment_completed_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'online_payment_started_at' => 'datetime',
            'online_payment_completed_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public static function nextInvoiceNumber(): string
    {
        $next = (int) static::withTrashed()->count() + 1;

        return 'CCI-' . now()->format('ym') . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
