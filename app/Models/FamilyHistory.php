<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'condition',
        'relationship',
        'status',
        'notes',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
