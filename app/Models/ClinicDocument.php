<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'owner_user_id',
        'uploaded_by',
        'document_type',
        'title',
        'notes',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
