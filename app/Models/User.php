<?php

namespace App\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Billable, HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'last_login_at',
        'email_verified_at',
        'email_verification_code',
        'email_verification_expires_at',
        'login_otp_code',
        'login_otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
        'login_otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'login_otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole($roles): bool
    {
        $userRole = strtolower((string) $this->role);
        $normalizedRoles = $this->normalizeRoles($roles);

        return in_array($userRole, $normalizedRoles, true);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function ownedDocuments(): HasMany
    {
        return $this->hasMany(ClinicDocument::class, 'owner_user_id');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(ClinicDocument::class, 'uploaded_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    private function normalizeRoles($roles): array
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if ($roles instanceof BackedEnum) {
            $roles = $roles->value;
        }

        if (is_string($roles)) {
            $roles = explode('|', $roles);
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $flatRoles = [];

        array_walk_recursive($roles, function ($role) use (&$flatRoles) {
            if ($role instanceof Collection) {
                foreach ($role->all() as $nestedRole) {
                    $this->normalizeRoleValue($nestedRole, $flatRoles);
                }

                return;
            }

            $this->normalizeRoleValue($role, $flatRoles);
        });

        return array_values(array_unique($flatRoles));
    }

    private function normalizeRoleValue($role, array &$flatRoles): void
    {
        if ($role instanceof BackedEnum) {
            $role = $role->value;
        }

        if (is_object($role) && isset($role->name)) {
            $role = $role->name;
        }

        if (!is_scalar($role)) {
            return;
        }

        foreach (explode('|', (string) $role) as $singleRole) {
            $singleRole = strtolower(trim($singleRole));

            if ($singleRole !== '') {
                $flatRoles[] = $singleRole;
            }
        }
    }
}
