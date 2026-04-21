<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_dashboard_does_not_show_revenue_or_payment_summary(): void
    {
        $receptionist = User::factory()->create([
            'role' => 'receptionist',
            'status' => 'active',
        ]);

        $this->actingAs($receptionist)
            ->get(route('dashboard', [], false))
            ->assertOk()
            ->assertSee('Book, update, and cancel appointments')
            ->assertDontSee('Month revenue')
            ->assertDontSee('Recent payments');
    }

    public function test_cashier_dashboard_shows_payment_duties_and_revenue(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'status' => 'active',
        ]);

        $this->actingAs($cashier)
            ->get(route('dashboard', [], false))
            ->assertOk()
            ->assertSee('Month revenue')
            ->assertSee('Record payments made by patients');
    }

    public function test_receptionist_cannot_manage_doctor_profiles_or_payments(): void
    {
        $receptionist = User::factory()->create([
            'role' => 'receptionist',
            'status' => 'active',
        ]);

        $this->actingAs($receptionist)
            ->get(route('doctors.create', [], false))
            ->assertForbidden();

        $this->actingAs($receptionist)
            ->get(route('payments.create', [], false))
            ->assertForbidden();

        $this->actingAs($receptionist)
            ->get(route('payments.index', [], false))
            ->assertForbidden();
    }

    public function test_patient_can_view_only_their_own_billing_records_and_cannot_record_payment(): void
    {
        config([
            'cashier.key' => null,
            'cashier.secret' => null,
        ]);

        $patientUser = User::factory()->create([
            'role' => 'patient',
            'status' => 'active',
        ]);
        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'patient_number' => 'CCP-26-1001',
            'first_name' => 'Patient',
            'last_name' => 'Owner',
            'status' => 'active',
        ]);
        $otherPatient = Patient::create([
            'patient_number' => 'CCP-26-1002',
            'first_name' => 'Other',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);
        $ownPayment = Payment::create([
            'patient_id' => $patient->id,
            'invoice_number' => 'CCI-2604-9001',
            'amount' => 65000,
            'payment_method' => 'Airtel Money',
            'status' => 'pending',
        ]);
        $otherPayment = Payment::create([
            'patient_id' => $otherPatient->id,
            'invoice_number' => 'CCI-2604-9002',
            'amount' => 45000,
            'payment_method' => 'Cash',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($patientUser)
            ->get(route('payments.index', [], false))
            ->assertOk()
            ->assertSee('Billing')
            ->assertSee($ownPayment->invoice_number)
            ->assertDontSee($otherPayment->invoice_number)
            ->assertDontSee('Record payment');

        $this->actingAs($patientUser)
            ->get(route('payments.show', $ownPayment, false))
            ->assertOk()
            ->assertSee('Online payment')
            ->assertSee('Mobile money')
            ->assertSee('Stripe keys are not configured');

        $this->actingAs($patientUser)
            ->get(route('payments.show', $otherPayment, false))
            ->assertForbidden();

        $this->actingAs($patientUser)
            ->get(route('payments.create', [], false))
            ->assertForbidden();
    }

    public function test_patient_can_submit_mobile_money_reference_for_pending_bill(): void
    {
        $patientUser = User::factory()->create([
            'role' => 'patient',
            'status' => 'active',
        ]);
        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'patient_number' => 'CCP-26-1004',
            'first_name' => 'Mobile',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);
        $payment = Payment::create([
            'patient_id' => $patient->id,
            'invoice_number' => 'CCI-2604-9004',
            'amount' => 50000,
            'payment_method' => 'Cash',
            'status' => 'pending',
        ]);

        $this->actingAs($patientUser)
            ->post(route('payments.mobile-money', $payment, false), [
                'payment_method' => 'Airtel Money',
                'reference' => 'AIRTEL-778899',
            ])
            ->assertRedirect(route('payments.show', $payment, false));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_method' => 'Airtel Money',
            'reference' => 'AIRTEL-778899',
            'status' => 'pending',
        ]);
    }

    public function test_stripe_checkout_amount_uses_ugx_minor_unit_format(): void
    {
        config(['cashier.currency' => 'ugx']);

        $payment = new Payment([
            'amount' => 120000,
        ]);

        $this->assertSame(12000000, app(BillingService::class)->stripeAmountFor($payment));
    }

    public function test_cashier_can_record_mobile_money_billing_payment(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'status' => 'active',
        ]);
        $patient = Patient::create([
            'patient_number' => 'CCP-26-1003',
            'first_name' => 'Billing',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);

        $this->actingAs($cashier)
            ->post(route('payments.store', [], false), [
                'patient_id' => $patient->id,
                'amount' => 75000,
                'payment_method' => 'MTN Mobile Money',
                'status' => 'paid',
                'reference' => 'MTN-TEST-001',
            ])
            ->assertRedirect(route('payments.index', [], false));

        $this->assertDatabaseHas('payments', [
            'patient_id' => $patient->id,
            'cashier_id' => $cashier->id,
            'amount' => '75000.00',
            'payment_method' => 'MTN Mobile Money',
            'status' => 'paid',
            'reference' => 'MTN-TEST-001',
        ]);
    }

    public function test_every_role_can_open_the_reports_page(): void
    {
        $roles = ['admin', 'receptionist', 'doctor', 'cashier', 'pharmacist', 'radiology', 'rn', 'pct', 'housekeeping', 'nurse', 'dietary', 'patient'];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'status' => 'active',
            ]);

            $this->actingAs($user)
                ->get(route('reports.index', [], false))
                ->assertOk()
                ->assertSee('Reports');
        }
    }

    public function test_reports_are_limited_to_role_appropriate_types(): void
    {
        $receptionist = User::factory()->create([
            'role' => 'receptionist',
            'status' => 'active',
        ]);

        $this->actingAs($receptionist)
            ->get(route('reports.index', [], false))
            ->assertOk()
            ->assertSee('Appointments')
            ->assertDontSee('Payments');

        $pharmacist = User::factory()->create([
            'role' => 'pharmacist',
            'status' => 'active',
        ]);

        $this->actingAs($pharmacist)
            ->get(route('reports.index', [], false))
            ->assertOk()
            ->assertSee('Prescription queue')
            ->assertSee('Drug inventory')
            ->assertDontSee('Payments');
    }
}
