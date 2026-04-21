<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    public function handle(Request $request): Response
    {
        $secret = config('cashier.webhook.secret');

        if (!filled($secret)) {
            return response('Stripe webhook secret is not configured.', 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid Stripe webhook signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $paymentId = $session->metadata->payment_id ?? null;

            if ($paymentId && $payment = Payment::find($paymentId)) {
                $this->billing->markStripeCheckoutResult($payment, $session);
            }
        }

        return response('Stripe webhook received.', 200);
    }
}
