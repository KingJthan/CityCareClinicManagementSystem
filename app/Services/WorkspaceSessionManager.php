<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceSessionManager
{
    public const CONTEXTS_KEY = 'citycare.workspaces';
    public const LAST_WORKSPACE_KEY = 'citycare.last_workspace';

    public function activate(Request $request, User $user): string
    {
        $contexts = $this->all($request);
        $workspace = $this->existingWorkspaceKey($contexts, $user) ?? $this->generateWorkspaceKey($user);

        $contexts[$workspace] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => (string) $user->role,
            'role_label' => $this->roleLabel((string) $user->role),
            'last_used_at' => now()->toIso8601String(),
        ];

        $request->session()->put(self::CONTEXTS_KEY, $contexts);
        $request->session()->put(self::LAST_WORKSPACE_KEY, $workspace);

        return $workspace;
    }

    public function all(Request $request): array
    {
        $contexts = $request->session()->get(self::CONTEXTS_KEY, []);

        return is_array($contexts) ? $contexts : [];
    }

    public function get(Request $request, string $workspace): ?array
    {
        $contexts = $this->all($request);

        return isset($contexts[$workspace]) && is_array($contexts[$workspace])
            ? $contexts[$workspace]
            : null;
    }

    public function userFor(Request $request, string $workspace): ?User
    {
        $context = $this->get($request, $workspace);

        if (!$context) {
            return null;
        }

        $user = User::find($context['user_id'] ?? null);

        if (!$user || !$user->isActive()) {
            $this->remove($request, $workspace);

            return null;
        }

        return $user;
    }

    public function touch(Request $request, string $workspace): void
    {
        $contexts = $this->all($request);

        if (!isset($contexts[$workspace])) {
            return;
        }

        $contexts[$workspace]['last_used_at'] = now()->toIso8601String();

        $request->session()->put(self::CONTEXTS_KEY, $contexts);
        $request->session()->put(self::LAST_WORKSPACE_KEY, $workspace);
    }

    public function remove(Request $request, string $workspace): array
    {
        $contexts = $this->all($request);

        unset($contexts[$workspace]);

        $request->session()->put(self::CONTEXTS_KEY, $contexts);

        $nextWorkspace = $this->lastWorkspaceKey($request, $contexts);

        if ($nextWorkspace) {
            $request->session()->put(self::LAST_WORKSPACE_KEY, $nextWorkspace);
        } else {
            $request->session()->forget(self::LAST_WORKSPACE_KEY);
        }

        return $contexts;
    }

    public function present(Request $request): array
    {
        $contexts = $this->all($request);

        uasort($contexts, function (array $left, array $right): int {
            return strcmp($right['last_used_at'] ?? '', $left['last_used_at'] ?? '');
        });

        $presented = [];

        foreach ($contexts as $workspace => $context) {
            $presented[] = [
                'key' => $workspace,
                'name' => $context['name'] ?? 'CityCare User',
                'role' => $context['role'] ?? 'staff',
                'role_label' => $context['role_label'] ?? $this->roleLabel((string) ($context['role'] ?? 'staff')),
            ];
        }

        return $presented;
    }

    public function lastWorkspaceKey(Request $request, ?array $contexts = null): ?string
    {
        $contexts ??= $this->all($request);

        if ($contexts === []) {
            return null;
        }

        $lastWorkspace = $request->session()->get(self::LAST_WORKSPACE_KEY);

        if (is_string($lastWorkspace) && isset($contexts[$lastWorkspace])) {
            return $lastWorkspace;
        }

        $ordered = $this->present($request);

        return $ordered[0]['key'] ?? array_key_first($contexts);
    }

    private function existingWorkspaceKey(array $contexts, User $user): ?string
    {
        foreach ($contexts as $workspace => $context) {
            if (($context['user_id'] ?? null) === $user->id && ($context['role'] ?? null) === $user->role) {
                return $workspace;
            }
        }

        return null;
    }

    private function generateWorkspaceKey(User $user): string
    {
        return Str::slug((string) $user->role) . '-' . Str::lower(Str::random(8));
    }

    private function roleLabel(string $role): string
    {
        return [
            'admin' => 'Administrator',
            'receptionist' => 'Receptionist',
            'doctor' => 'Doctor',
            'cashier' => 'Cashier',
            'pharmacist' => 'Pharmacist',
            'radiology' => 'Radiology',
            'rn' => 'RN',
            'pct' => 'PCT',
            'housekeeping' => 'House Keeping',
            'nurse' => 'Nurse',
            'dietary' => 'Dietary',
            'patient' => 'Patient',
        ][$role] ?? ucfirst($role);
    }
}
