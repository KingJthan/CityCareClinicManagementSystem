<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicInquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'request_type',
        'preferred_date',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
        ];
    }
}
