<?php

namespace Tests\Feature;

use App\Models\BillingProduct;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
