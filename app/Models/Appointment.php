<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    public const ACTIVE_STATUSES = ['pending', 'scheduled', 'available', 'checked_in'];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'department_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'visit_type',
        'reason',
        'internal_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function radiologyOrders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class);
    }

    public function labResults(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCheckInWindowOpen(): bool
    {
        if (!$this->appointment_date || !$this->start_time) {
            return false;
        }

        $startAt = $this->appointment_date->copy()->setTimeFromTimeString(substr($this->start_time, 0, 5));

        return now()->between($startAt->copy()->subMinutes(30), $startAt->copy()->addHours(2));
    }

    public static function markAvailableForCheckIn(): void
    {
        $now = now();

        static::where('status', 'scheduled')
            ->whereDate('appointment_date', $now->toDateString())
            ->whereTime('start_time', '<=', $now->copy()->addMinutes(30)->format('H:i:s'))
            ->whereTime('end_time', '>=', $now->format('H:i:s'))
            ->update(['status' => 'available']);
    }
}
