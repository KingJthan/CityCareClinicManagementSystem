<?php

namespace Tests\Feature;

use App\Models\BillingProduct;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CheckoutCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_service_can_be_added_to_cart_and_submitted_for_cashier_verification(): void
    {
        $product = BillingProduct::create([
            'code' => 'TEST-CONS',
            'name' => 'Test consultation',
            'category' => 'Consultation',
            'description' => 'A test consultation service.',
            'price' => 85000,
            'status' => 'active',
        ]);

        $this->post(route('cart.add', $product, false), ['quantity' => 2])
            ->assertRedirect(route('cart.index', [], false))
            ->assertSessionHas("care_cart.{$product->id}", 2);

        $this->get(route('cart.index', [], false))
            ->assertOk()
            ->assertSee('Test consultation')
            ->assertSee('170,000');

        $this->withSession(['care_cart' => [$product->id => 2]])
            ->post(route('shop.checkout.store', [], false), [
                'customer_name' => 'Sarah Visitor',
                'customer_email' => 'sarah.visitor@example.test',
                'customer_phone' => '+256700555123',
                'payment_method' => 'MTN Mobile Money',
                'reference' => 'MTN-778899',
            ])
            ->assertRedirect()
            ->assertSessionMissing('care_cart');

        $payment = Payment::firstOrFail();

        $this->assertSame('MTN Mobile Money', $payment->payment_method);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('MTN-778899', $payment->reference);

        $this->assertDatabaseHas('payment_items', [
            'payment_id' => $payment->id,
            'billing_product_id' => $product->id,
            'quantity' => 2,
            'line_total' => '170000.00',
        ]);
    }

    public function test_card_checkout_requires_stripe_keys_before_creating_payment(): void
    {
        config([
            'cashier.key' => null,
            'cashier.secret' => null,
        ]);

        $product = BillingProduct::create([
            'code' => 'TEST-LAB',
            'name' => 'Test lab work',
            'category' => 'Diagnostics',
            'description' => 'A test diagnostic service.',
            'price' => 60000,
            'status' => 'active',
        ]);

        $this->withSession(['care_cart' => [$product->id => 1]])
            ->from(route('shop.checkout', [], false))
            ->post(route('shop.checkout.store', [], false), [
                'customer_name' => 'Card Patient',
                'customer_email' => 'card.patient@example.test',
                'customer_phone' => '+256700555456',
                'payment_method' => 'Stripe Checkout',
            ])
            ->assertRedirect(route('shop.checkout', [], false))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_patient_can_use_care_shop_inside_workspace_portal(): void
    {
        $product = BillingProduct::create([
            'code' => 'TEST-WORK',
            'name' => 'Workspace consultation',
            'category' => 'Consultation',
            'description' => 'A workspace care service.',
            'price' => 70000,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'name' => 'Patient Portal User',
            'email' => 'patient@citycare.test',
            'password' => Hash::make('citycare456'),
            'role' => 'patient',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Patient::create([
            'user_id' => $user->id,
            'patient_number' => 'P-0001',
            'first_name' => 'Patient',
            'last_name' => 'Portal',
            'email' => $user->email,
            'phone' => '+256700555222',
            'status' => 'active',
        ]);

        $workspace = $this->workspaceFromRedirect($this->post(route('login.store', [], false), [
            'email' => 'patient@citycare.test',
            'password' => 'citycare456',
            'expected_role' => 'patient',
        ]));

        $this->get(route('workspace.shop.index', ['workspace' => $workspace], false))
            ->assertOk()
            ->assertSee('Select services and check out securely');

        $this->post(route('workspace.cart.add', ['workspace' => $workspace, 'product' => $product], false), [
            'quantity' => 1,
        ])
            ->assertRedirect(route('workspace.cart.index', ['workspace' => $workspace], false))
            ->assertSessionHas("care_cart.{$product->id}", 1);

        $this->get(route('workspace.cart.index', ['workspace' => $workspace], false))
            ->assertOk()
            ->assertSee('Workspace consultation')
            ->assertSee('70,000');
    }

    private function workspaceFromRedirect(TestResponse $response): string
    {
        $response->assertRedirect();

        $path = parse_url($response->headers->get('Location'), PHP_URL_PATH) ?: '';

        $this->assertMatchesRegularExpression('#^/workspace/[A-Za-z0-9\-]+/dashboard$#', $path);

        return explode('/', trim($path, '/'))[1];
    }
}
