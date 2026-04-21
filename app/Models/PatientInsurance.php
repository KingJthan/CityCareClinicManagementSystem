<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInsurance extends Model
{
    protected $fillable = [
        'patient_id',
        'provider_name',
        'policy_number',
        'member_number',
        'coverage_type',
        'status',
        'valid_until',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
