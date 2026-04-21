<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'drug_id',
        'prescribed_by',
        'dispensed_by',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'status',
        'dispensed_at',
        'pharmacist_notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function prescriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
