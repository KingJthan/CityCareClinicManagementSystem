<?php

namespace App\Http\Controllers;

use App\Models\BillingProduct;
use App\Models\Payment;
use App\Rules\PhoneNumber;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Cashier;

class CartController extends Controller
{
    private const CART_KEY = 'care_cart';

    public function __construct(private BillingService $billing)
    {
    }

    public function shop(Request $request)
    {
        return view('shop.index', [
            'cartCount' => $this->billing->cartCount($this->cartContents($request)),
            'products' => $this->billing->activeProducts(),
        ]);
    }

    public function add(Request $request, BillingProduct $product): RedirectResponse
    {
        abort_unless($product->status === 'active', 404);

        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $cart = $this->cartContents($request);
        $quantity = (int) ($data['quantity'] ?? 1);
        $cart[$product->id] = min(10, ((int) ($cart[$product->id] ?? 0)) + $quantity);

        $request->session()->put(self::CART_KEY, $cart);

        return redirect()->to(workspace_route('cart.index'))->with('success', $product->name . ' added to your cart.');
    }

    public function workspaceAdd(Request $request, string $workspace, BillingProduct $product): RedirectResponse
    {
        return $this->add($request, $product);
    }

    public function cart(Request $request)
    {
        $items = $this->billing->cartItems($this->cartContents($request));

        return view('shop.cart', [
            'items' => $items,
            'total' => $this->billing->cartTotal($items),
        ]);
    }

    public function update(Request $request, BillingProduct $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $cart = $this->cartContents($request);

        if ((int) $data['quantity'] === 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = (int) $data['quantity'];
        }

        $request->session()->put(self::CART_KEY, $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function workspaceUpdate(Request $request, string $workspace, BillingProduct $product): RedirectResponse
    {
        return $this->update($request, $product);
    }

    public function remove(Request $request, BillingProduct $product): RedirectResponse
    {
        $cart = $this->cartContents($request);
        unset($cart[$product->id]);
        $request->session()->put(self::CART_KEY, $cart);

        return back()->with('success', $product->name . ' removed from your cart.');
    }

    public function workspaceRemove(Request $request, string $workspace, BillingProduct $product): RedirectResponse
    {
        return $this->remove($request, $product);
    }

    public function checkout(Request $request)
    {
        $items = $this->billing->cartItems($this->cartContents($request));

        if ($items->isEmpty()) {
            return redirect()->to(workspace_route('shop.index'))->with('error', 'Choose at least one service before checkout.');
        }

        $user = $request->user();
        $patient = $user?->patientProfile;

        return view('shop.checkout', [
            'items' => $items,
            'mobileMoneyMethods' => $this->billing->mobileMoneyMethods(),
            'patient' => $patient,
            'stripeConfigured' => $this->billing->stripeConfigured(),
            'total' => $this->billing->cartTotal($items),
            'user' => $user,
        ]);
    }

    public function processCheckout(Request $request): RedirectResponse
    {
        $items = $this->billing->cartItems($this->cartContents($request));

        if ($items->isEmpty()) {
            return redirect()->to(workspace_route('shop.index'))->with('error', 'Choose at least one service before checkout.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'customer_phone' => ['required', new PhoneNumber],
            'payment_method' => ['required', Rule::in(['Stripe Checkout', 'MTN Mobile Money', 'Airtel Money', 'Bank Deposit'])],
            'reference' => [
                Rule::requiredIf(fn () => $request->input('payment_method') !== 'Stripe Checkout'),
                'nullable',
                'string',
                'min:4',
                'max:120',
            ],
        ]);

        if ($data['payment_method'] === 'Stripe Checkout' && !$this->billing->stripeConfigured()) {
            return back()
                ->withInput()
                ->with('error', 'Stripe keys are not configured. Add STRIPE_KEY and STRIPE_SECRET before starting card checkout.');
        }

        $payment = $this->billing->createCartPayment($data, $items, $data['payment_method'], $request->user());

        if ($data['payment_method'] === 'Stripe Checkout') {
            try {
                return $this->billing->createCartStripeCheckout(
                    $payment,
                    $items,
                    $data,
                    workspace_route('shop.checkout.success', $payment) . '?session_id={CHECKOUT_SESSION_ID}',
                    workspace_route('shop.checkout.cancel', $payment)
                )->redirect();
            } catch (\Throwable $exception) {
                report($exception);

                return back()
                    ->withInput()
                    ->with('error', 'Stripe Checkout could not be started. Please verify your Stripe account settings and keys.');
            }
        }

        $request->session()->forget(self::CART_KEY);
        $request->session()->put('last_checkout_payment', $payment->id);

        return redirect()
            ->to(workspace_route('shop.checkout.success', $payment))
            ->with('success', 'Checkout submitted. The cashier will verify your payment reference.');
    }

    public function success(Request $request, Payment $payment)
    {
        if ($request->filled('session_id')) {
            if (!$this->billing->stripeConfigured()) {
                return redirect()->to(workspace_route('cart.index'))->with('error', 'Stripe keys are not configured, so the checkout session cannot be verified.');
            }

            try {
                $session = Cashier::stripe()->checkout->sessions->retrieve(
                    $request->query('session_id'),
                    ['expand' => ['payment_intent']]
                );

                if (!$this->billing->stripeSessionBelongsToPayment($session, $payment)) {
                    abort(403);
                }

                $this->billing->markStripeCheckoutResult($payment, $session);
                $request->session()->forget(self::CART_KEY);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->to(workspace_route('cart.index'))->with('error', 'Stripe payment confirmation could not be verified.');
            }
        }

        return view('shop.success', [
            'payment' => $payment->fresh(['patient', 'items.billingProduct']),
        ]);
    }

    public function workspaceSuccess(Request $request, string $workspace, Payment $payment)
    {
        return $this->success($request, $payment);
    }

    public function cancel(Payment $payment): RedirectResponse
    {
        return redirect()
            ->to(workspace_route('cart.index'))
            ->with('error', 'Stripe Checkout was cancelled. Your cart is still available if you want to try again.');
    }

    public function workspaceCancel(string $workspace, Payment $payment): RedirectResponse
    {
        return $this->cancel($payment);
    }

    private function cartContents(Request $request): array
    {
        return $request->session()->get(self::CART_KEY, []);
    }
}
