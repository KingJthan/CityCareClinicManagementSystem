<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'recorded_by',
        'blood_pressure',
        'temperature_c',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight_kg',
        'recorded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature_c' => 'decimal:1',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
