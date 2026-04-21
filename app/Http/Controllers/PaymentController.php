<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    public function index(Request $request)
    {
        return view('payments.index', [
            'canRecordPayments' => $this->billing->canRecordPayments($request->user()),
            'paymentMethods' => $this->billing->paymentMethods(),
            'payments' => $this->billing->paymentsFor($request->user(), $request->only(['search', 'status', 'payment_method']))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'paymentStatuses' => $this->billing->paymentStatuses(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensureCanRecord($request);

        return view('payments.form', $this->formData(new Payment(['status' => 'pending'])));
    }

    public function store(Request $request)
    {
        $this->ensureCanRecord($request);
        $data = $this->validated($request);

        $this->billing->createCashierPayment($data, $request->user());

        return redirect()->route('payments.index')->with('success', 'Payment record created.');
    }

    public function show(Request $request, Payment $payment)
    {
        $this->ensureCanView($request, $payment);
        $payment->load(['patient', 'appointment.doctor.user', 'cashier', 'items.billingProduct']);

        return view('payments.show', [
            'canPayOnline' => $this->billing->canPatientPayOnline($request->user(), $payment),
            'mobileMoneyMethods' => $this->billing->mobileMoneyMethods(),
            'payment' => $payment,
            'stripeConfigured' => $this->billing->stripeConfigured(),
        ]);
    }

    public function edit(Request $request, Payment $payment)
    {
        $this->ensureCanRecord($request);

        return view('payments.form', $this->formData($payment));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->ensureCanRecord($request);
        $data = $this->validated($request);

        $this->billing->updateCashierPayment($payment, $data, $request->user());

        return redirect()->route('payments.show', $payment)->with('success', 'Payment record updated.');
    }

    public function destroy(Request $request, Payment $payment)
    {
        $this->ensureCanRecord($request);
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Payment archived.');
    }

    public function stripeCheckout(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureCanPatientPay($request, $payment);

        if (!$this->billing->stripeConfigured()) {
            return back()->with('error', 'Stripe is not configured. Add STRIPE_KEY and STRIPE_SECRET to .env first.');
        }

        try {
            return $this->billing->createStripeCheckout($payment, $request->user())->redirect();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Stripe Checkout could not be started. Please verify the Stripe keys and try again.');
        }
    }

    public function stripeSuccess(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureCanPatientPay($request, $payment, allowStartedCheckout: true);

        if (!$request->filled('session_id')) {
            return redirect()->route('payments.show', $payment)->with('error', 'Stripe did not return a checkout session.');
        }

        try {
            $session = $request->user()->stripe()->checkout->sessions->retrieve(
                $request->query('session_id'),
                ['expand' => ['payment_intent']]
            );

            if (!$this->billing->stripeSessionBelongsToPayment($session, $payment)) {
                abort(403);
            }

            $this->billing->markStripeCheckoutResult($payment, $session);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('payments.show', $payment)->with('error', 'Stripe payment confirmation could not be verified.');
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Stripe payment received successfully.');
    }

    public function stripeCancel(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureCanView($request, $payment);

        return redirect()->route('payments.show', $payment)->with('error', 'Stripe Checkout was cancelled. You can try again when ready.');
    }

    public function mobileMoney(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureCanPatientPay($request, $payment);

        $data = $request->validate([
            'payment_method' => ['required', Rule::in($this->billing->mobileMoneyMethods())],
            'reference' => ['required', 'string', 'min:4', 'max:120'],
        ]);

        $this->billing->markMobileMoneyInitiated($payment, $data);

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Mobile money reference submitted. The cashier will verify and complete the receipt.');
    }

    private function formData(Payment $payment): array
    {
        return [
            'payment' => $payment,
            'appointments' => $this->billing->billableAppointments(),
            'patients' => $this->billing->activePatients(),
            'paymentMethods' => $this->billing->paymentMethods(),
            'paymentStatuses' => $this->billing->paymentStatuses(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'patient_id' => ['required_without:appointment_id', 'nullable', 'exists:patients,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in($this->billing->paymentMethods())],
            'status' => ['required', Rule::in($this->billing->paymentStatuses())],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function ensureCanRecord(Request $request): void
    {
        if (!$this->billing->canRecordPayments($request->user())) {
            abort(403);
        }
    }

    private function ensureCanView(Request $request, Payment $payment): void
    {
        if (!$this->billing->canViewPayment($request->user(), $payment)) {
            abort(403);
        }
    }

    private function ensureCanPatientPay(Request $request, Payment $payment, bool $allowStartedCheckout = false): void
    {
        $canPay = $this->billing->canPatientPayOnline($request->user(), $payment);

        if ($allowStartedCheckout && $request->user()->hasRole('patient') && $request->user()->patientProfile?->id === $payment->patient_id) {
            $canPay = $canPay || $payment->payment_method === 'Stripe Checkout';
        }

        if (!$canPay) {
            abort(403);
        }
    }

    public static function paymentMethods(): array
    {
        return app(BillingService::class)->paymentMethods();
    }
}
