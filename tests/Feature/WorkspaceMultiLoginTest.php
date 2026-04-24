<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WorkspaceMultiLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_role_logins_can_stay_active_in_the_same_browser_session(): void
    {
        User::factory()->create([
            'name' => 'CityCare Administrator',
            'email' => 'admin@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Daniel Cashier',
            'email' => 'cashier@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'cashier',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $adminResponse = $this->post(route('login.store', [], false), [
            'email' => 'admin@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'admin',
        ]);

        $adminWorkspace = $this->workspaceFromRedirect($adminResponse);

        $cashierResponse = $this->post(route('login.store', [], false), [
            'email' => 'cashier@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'cashier',
        ]);

        $cashierWorkspace = $this->workspaceFromRedirect($cashierResponse);

        $this->assertNotSame($adminWorkspace, $cashierWorkspace);

        $this->get(route('workspace.dashboard', ['workspace' => $adminWorkspace], false))
            ->assertOk()
            ->assertSee('Administrator Dashboard')
            ->assertSee('Month revenue');

        $this->get(route('workspace.dashboard', ['workspace' => $cashierWorkspace], false))
            ->assertOk()
            ->assertSee('Cashier Dashboard')
            ->assertSee('Record payments made by patients');
    }

    public function test_logging_out_one_workspace_keeps_the_other_workspace_active(): void
    {
        User::factory()->create([
            'name' => 'CityCare Administrator',
            'email' => 'admin@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Daniel Cashier',
            'email' => 'cashier@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'cashier',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $adminWorkspace = $this->workspaceFromRedirect($this->post(route('login.store', [], false), [
            'email' => 'admin@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'admin',
        ]));

        $cashierWorkspace = $this->workspaceFromRedirect($this->post(route('login.store', [], false), [
            'email' => 'cashier@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'cashier',
        ]));

        $this->post(route('workspace.logout', ['workspace' => $adminWorkspace], false))
            ->assertRedirect(route('workspace.dashboard', ['workspace' => $cashierWorkspace], false));

        $this->get(route('workspace.dashboard', ['workspace' => $cashierWorkspace], false))
            ->assertOk()
            ->assertSee('Cashier Dashboard');

        $this->get(route('workspace.dashboard', ['workspace' => $adminWorkspace], false))
            ->assertRedirect(route('workspace.dashboard', ['workspace' => $cashierWorkspace], false));
    }

    private function workspaceFromRedirect(TestResponse $response): string
    {
        $response->assertRedirect();

        $path = parse_url($response->headers->get('Location'), PHP_URL_PATH) ?: '';

        $this->assertMatchesRegularExpression('#^/workspace/[A-Za-z0-9\-]+/dashboard$#', $path);

        return explode('/', trim($path, '/'))[1];
    }
}
