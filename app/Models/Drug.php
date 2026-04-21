<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drug extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'drug_category_id',
        'name',
        'generic_name',
        'strength',
        'dosage_form',
        'unit',
        'stock_quantity',
        'reorder_level',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DrugCategory::class, 'drug_category_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
