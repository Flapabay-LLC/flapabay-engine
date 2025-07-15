<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        // $sig_header = $request->header('Stripe-Signature'); // Signature verification disabled for testing
        $secret = config('services.stripe.webhook_secret');

        try {
            // Commented out signature verification for local testing/debugging only!
            // $event = Webhook::constructEvent($payload, $sig_header, $secret);
            $event = json_decode($payload); // UNSAFE: Only use this for local development!
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('💰 Payment succeeded:', (array) $paymentIntent);
                // Handle logic (e.g., update order status)
                // 1. create a booking record
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                Log::warning('❌ Payment intent failed:', (array) $paymentIntent);
                // Handle failed payment intent logic
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                Log::warning('⚠️ Payment failed:', (array) $invoice);
                // Handle failed invoice logic
                break;

            case 'charge.succeeded':
                $charge = $event->data->object;
                Log::info('💳 Charge succeeded:', (array) $charge);
                // Handle successful charge logic
                break;

            case 'charge.failed':
                $charge = $event->data->object;
                Log::warning('❌ Charge failed:', (array) $charge);
                // Handle failed charge logic
                break;

            case 'balance.available':
                $balance = $event->data->object;
                Log::info('💵 Balance available:', (array) $balance);
                // Handle balance available logic
                break;

            case 'customer.subscription.created':
                $subscription = $event->data->object;
                Log::info('📝 Subscription created:', (array) $subscription);
                // Handle new subscription logic
                break;

            // Add more event cases as needed

            default:
                Log::info('📩 Received unknown event type', ['type' => $event->type]);
        }

        return new Response('Webhook handled', 200);
    }
}
