<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BillingProduct;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Checkout;

class BillingService
{
    public const PAYMENT_METHODS = [
        'Cash',
        'MTN Mobile Money',
        'Airtel Money',
        'Card Payment',
        'Bank Deposit',
        'Insurance',
        'Stripe Checkout',
    ];

    public const MOBILE_MONEY_METHODS = [
        'MTN Mobile Money',
        'Airtel Money',
    ];

    public const PAYMENT_STATUSES = [
        'pending',
        'paid',
        'waived',
        'refunded',
    ];

    public function paymentMethods(): array
    {
        return self::PAYMENT_METHODS;
    }

    public function paymentStatuses(): array
    {
        return self::PAYMENT_STATUSES;
    }

    public function canRecordPayments(User $user): bool
    {
        return $user->hasRole(['admin', 'cashier']);
    }

    public function canViewPayment(User $user, Payment $payment): bool
    {
        if ($user->hasRole(['admin', 'cashier'])) {
            return true;
        }

        return $user->hasRole('patient') && $user->patientProfile?->id === $payment->patient_id;
    }

    public function canPatientPayOnline(User $user, Payment $payment): bool
    {
        return $user->hasRole('patient')
            && $user->patientProfile?->id === $payment->patient_id
            && $payment->status === 'pending'
            && (float) $payment->amount > 0;
    }

    public function stripeConfigured(): bool
    {
        return filled(config('cashier.key')) && filled(config('cashier.secret'));
    }

    public function paymentsFor(User $user, array $filters = []): Builder
    {
        $payments = Payment::with(['patient', 'appointment.doctor.user', 'cashier'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($patient) => $patient
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method));

        if ($user->hasRole('patient')) {
            $payments->where('patient_id', $user->patientProfile?->id);
        }

        return $payments;
    }

    public function activePatients(): Collection
    {
        return Patient::where('status', 'active')->orderBy('first_name')->get();
    }

    public function activeProducts(): Collection
    {
        return BillingProduct::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    public function cartItems(array $cart): Collection
    {
        $quantities = collect($cart)
            ->mapWithKeys(fn ($quantity, $productId) => [(int) $productId => max(1, min(10, (int) $quantity))])
            ->filter();

        if ($quantities->isEmpty()) {
            return collect();
        }

        $products = BillingProduct::active()
            ->whereIn('id', $quantities->keys())
            ->get()
            ->keyBy('id');

        return $quantities
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                if (!$product) {
                    return null;
                }

                $unitAmount = (float) $product->price;

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $unitAmount * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function cartCount(array $cart): int
    {
        return collect($cart)->sum(fn ($quantity) => max(0, (int) $quantity));
    }

    public function cartTotal(Collection $items): float
    {
        return (float) $items->sum('line_total');
    }

    public function billableAppointments(): Collection
    {
        return Appointment::with(['patient', 'doctor.user'])
            ->whereIn('status', ['scheduled', 'available', 'checked_in', 'completed'])
            ->latest('appointment_date')
            ->take(100)
            ->get();
    }

    public function createCashierPayment(array $data, User $cashier): Payment
    {
        return Payment::create($this->prepareBillingData($data) + [
            'invoice_number' => Payment::nextInvoiceNumber(),
            'cashier_id' => $cashier->id,
        ]);
    }

    public function updateCashierPayment(Payment $payment, array $data, User $cashier): Payment
    {
        $payment->update($this->prepareBillingData($data) + [
            'cashier_id' => $cashier->id,
        ]);

        return $payment;
    }

    public function createCartPayment(array $customer, Collection $items, string $paymentMethod, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($customer, $items, $paymentMethod, $user) {
            $patient = $this->patientForCheckout($customer, $user);
            $amount = $this->cartTotal($items);
            $note = $paymentMethod === 'Stripe Checkout'
                ? 'Created from care services cart. Awaiting Stripe card checkout.'
                : 'Created from care services cart. Awaiting cashier verification for ' . $paymentMethod . '.';

            $payment = Payment::create([
                'patient_id' => $patient->id,
                'invoice_number' => Payment::nextInvoiceNumber(),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'reference' => $customer['reference'] ?? null,
                'notes' => $note,
            ]);

            foreach ($items as $item) {
                $product = $item['product'];

                $payment->items()->create([
                    'billing_product_id' => $product->id,
                    'description' => $product->name,
                    'unit_amount' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return $payment->load(['patient', 'items.billingProduct']);
        });
    }

    public function createStripeCheckout(Payment $payment, User $patientUser): Checkout
    {
        $metadata = [
            'payment_id' => (string) $payment->id,
            'invoice_number' => $payment->invoice_number,
            'patient_id' => (string) $payment->patient_id,
        ];

        $checkout = $patientUser->checkoutCharge(
            $this->stripeAmountFor($payment),
            'CityCare invoice ' . $payment->invoice_number,
            1,
            [
                'success_url' => route('payments.stripe.success', $payment) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payments.stripe.cancel', $payment),
                'metadata' => $metadata,
                'payment_intent_data' => [
                    'metadata' => $metadata,
                ],
            ],
            [
                'name' => $payment->patient->full_name,
                'email' => $payment->patient->email ?: $patientUser->email,
                'phone' => $payment->patient->phone ?: $patientUser->phone,
            ]
        );

        $session = $checkout->asStripeCheckoutSession();

        $payment->update([
            'payment_method' => 'Stripe Checkout',
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_status' => $session->payment_status,
            'online_payment_started_at' => now(),
        ]);

        return $checkout;
    }

    public function createCartStripeCheckout(
        Payment $payment,
        Collection $items,
        array $customer,
        string $successUrl,
        string $cancelUrl
    ): Checkout
    {
        $metadata = [
            'payment_id' => (string) $payment->id,
            'invoice_number' => $payment->invoice_number,
            'cart_checkout' => 'true',
        ];

        $lineItems = $items->map(fn ($item) => [
            'price_data' => [
                'currency' => strtolower(config('cashier.currency', 'ugx')),
                'product_data' => [
                    'name' => $item['product']->name,
                    'description' => $item['product']->description,
                ],
                'unit_amount' => $this->stripeAmountForAmount((float) $item['product']->price),
            ],
            'quantity' => $item['quantity'],
        ])->values()->all();

        $checkout = Checkout::guest()->create($lineItems, [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $customer['customer_email'],
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
            'payment_method_types' => ['card'],
            'phone_number_collection' => ['enabled' => true],
        ]);

        $session = $checkout->asStripeCheckoutSession();

        $payment->update([
            'payment_method' => 'Stripe Checkout',
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_status' => $session->payment_status,
            'online_payment_started_at' => now(),
        ]);

        return $checkout;
    }

    public function markStripeCheckoutResult(Payment $payment, object $session): Payment
    {
        $paymentIntentId = is_string($session->payment_intent)
            ? $session->payment_intent
            : ($session->payment_intent->id ?? null);

        $data = [
            'payment_method' => 'Stripe Checkout',
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_payment_status' => $session->payment_status,
            'reference' => $paymentIntentId ?: $session->id,
        ];

        if ($session->payment_status === 'paid') {
            $data['status'] = 'paid';
            $data['paid_at'] = now();
            $data['online_payment_completed_at'] = now();
        }

        $payment->update($data);

        return $payment;
    }

    public function markMobileMoneyInitiated(Payment $payment, array $data): Payment
    {
        $note = 'Patient submitted ' . $data['payment_method'] . ' reference for cashier verification.';

        if (filled($payment->notes)) {
            $note = trim($payment->notes) . PHP_EOL . $note;
        }

        $payment->update([
            'payment_method' => $data['payment_method'],
            'reference' => $data['reference'],
            'notes' => $note,
        ]);

        return $payment;
    }

    public function stripeAmountFor(Payment $payment): int
    {
        return $this->stripeAmountForAmount((float) $payment->amount);
    }

    public function stripeAmountForAmount(float $amount): int
    {
        $currency = strtolower(config('cashier.currency', 'ugx'));

        if (in_array($currency, $this->zeroDecimalCurrencies(), true) && $currency !== 'ugx') {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    public function mobileMoneyMethods(): array
    {
        return self::MOBILE_MONEY_METHODS;
    }

    public function stripeSessionBelongsToPayment(object $session, Payment $payment): bool
    {
        return (string) Arr::get($session->metadata?->toArray() ?? [], 'payment_id') === (string) $payment->id;
    }

    private function patientForCheckout(array $customer, ?User $user = null): Patient
    {
        if ($user?->hasRole('patient') && $user->patientProfile) {
            $user->patientProfile->update([
                'phone' => $customer['customer_phone'],
                'email' => $customer['customer_email'],
            ]);

            return $user->patientProfile;
        }

        $existing = Patient::where('email', $customer['customer_email'])->first();
        [$firstName, $lastName] = $this->splitCustomerName($customer['customer_name']);

        if ($existing) {
            $existing->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $customer['customer_phone'],
                'status' => 'active',
            ]);

            return $existing;
        }

        return Patient::create([
            'patient_number' => Patient::nextPatientNumber(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $customer['customer_phone'],
            'email' => $customer['customer_email'],
            'status' => 'active',
        ]);
    }

    private function splitCustomerName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [
            $parts[0] ?: 'Patient',
            $parts[1] ?? 'Customer',
        ];
    }

    private function prepareBillingData(array $data): array
    {
        if (!empty($data['appointment_id'])) {
            $appointment = Appointment::findOrFail($data['appointment_id']);
            $data['patient_id'] = $appointment->patient_id;
        }

        if (($data['status'] ?? null) === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }

    private function zeroDecimalCurrencies(): array
    {
        return [
            'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
            'rwf', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
        ];
    }
}
