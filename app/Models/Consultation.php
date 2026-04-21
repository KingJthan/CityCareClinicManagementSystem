<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'symptoms',
        'diagnosis',
        'treatment_plan',
        'prescription',
        'next_visit_date',
    ];

    protected function casts(): array
    {
        return [
            'next_visit_date' => 'date',
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
}
