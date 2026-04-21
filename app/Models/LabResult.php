<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    protected $fillable = [
        'patient_id',
        'appointment_id',
        'ordered_by',
        'category',
        'test_name',
        'result_value',
        'unit',
        'reference_range',
        'status',
        'resulted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'resulted_at' => 'datetime',
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

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'ordered_by');
    }
}
