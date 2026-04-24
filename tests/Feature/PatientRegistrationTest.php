<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registration_creates_login_and_patient_profile(): void
    {
        $response = $this->post(route('register.store', [], false), [
            'name' => 'Grace Patient',
            'email' => 'grace.patient@example.com',
            'gender' => 'Female',
            'phone' => '5550101234',
            'address' => 'Kampala',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('verification.form', [], false));
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'grace.patient@example.com',
            'role' => 'patient',
        ]);

        $user = User::where('email', 'grace.patient@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->email_verification_code);

        $patient = Patient::where('email', 'grace.patient@example.com')->firstOrFail();

        $this->assertSame('CCP-26-0001', $patient->patient_number);
        $this->assertSame('Grace', $patient->first_name);
        $this->assertSame('Patient', $patient->last_name);

        $dashboardResponse = $this->post(route('verification.verify', [], false), [
            'code' => $user->email_verification_code,
        ]);

        $workspace = $this->assertRedirectsToWorkspaceDashboard($dashboardResponse);

        $this->assertGuest();
        $this->assertSame($workspace, session('citycare.last_workspace'));
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->get(route('workspace.dashboard', ['workspace' => $workspace], false))
            ->assertOk()
            ->assertSee('Patient Dashboard');
    }

    public function test_patient_registration_rejects_short_phone_numbers(): void
    {
        $this->post(route('register.store', [], false), [
            'name' => 'Short Phone',
            'email' => 'short.phone@example.com',
            'phone' => '555',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseMissing('users', [
            'email' => 'short.phone@example.com',
        ]);
    }

    public function test_verified_user_login_requires_otp_before_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'cashier.login@example.com',
            'password' => Hash::make('citycare456'),
            'role' => 'cashier',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->post(route('login.store', [], false), [
            'email' => 'cashier.login@example.com',
            'password' => 'citycare456',
            'expected_role' => 'cashier',
        ])->assertRedirect(route('otp.form', [], false));

        $this->assertGuest();
        $this->assertNotNull($user->fresh()->login_otp_code);

        $dashboardResponse = $this->post(route('otp.verify', [], false), [
            'code' => $user->fresh()->login_otp_code,
        ]);

        $workspace = $this->assertRedirectsToWorkspaceDashboard($dashboardResponse);

        $this->assertGuest();
        $this->assertNull($user->fresh()->login_otp_code);

        $this->get(route('workspace.dashboard', ['workspace' => $workspace], false))
            ->assertOk()
            ->assertSee('Cashier Dashboard');
    }

    public function test_demo_citycare_accounts_login_without_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $dashboardResponse = $this->post(route('login.store', [], false), [
            'email' => 'admin@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'admin',
        ]);

        $workspace = $this->assertRedirectsToWorkspaceDashboard($dashboardResponse);

        $this->assertGuest();
        $this->assertNull($user->fresh()->login_otp_code);

        $this->get(route('workspace.dashboard', ['workspace' => $workspace], false))
            ->assertOk()
            ->assertSee('Administrator Dashboard');
    }

    private function assertRedirectsToWorkspaceDashboard(TestResponse $response): string
    {
        $response->assertRedirect();

        $path = parse_url($response->headers->get('Location'), PHP_URL_PATH) ?: '';

        $this->assertMatchesRegularExpression('#^/workspace/[A-Za-z0-9\-]+/dashboard$#', $path);

        return explode('/', trim($path, '/'))[1];
    }
}
