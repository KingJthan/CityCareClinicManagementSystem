<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'staff_number',
        'license_number',
        'specialization',
        'consultation_fee',
        'shift_starts_at',
        'shift_ends_at',
        'slot_minutes',
        'working_days',
        'room',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'working_days' => 'array',
            'consultation_fee' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function radiologyOrders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return 'Dr. ' . $this->user->name;
    }
}
