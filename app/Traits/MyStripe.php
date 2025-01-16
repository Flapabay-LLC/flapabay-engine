<?php

namespace App\Traits;

use Stripe\StripeClient;

trait MyStripe
{
    protected $stripe;

    public function __construct()
    {
        // Initialize Stripe client with your secret key
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Authentication - Verify API connection by retrieving account details
     */
    public function authenticate(): bool
    {
        try {
            $account = $this->stripe->accounts->retrieve();
            return isset($account->id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a connected account
     *
     * @param array $data
     * @return \Stripe\Account
     */
    public function createConnectedAccount(array $data): \Stripe\Account
    {
        return $this->stripe->accounts->create($data);
    }

    /**
     * Retrieve a connected account
     *
     * @param string $accountId
     * @return \Stripe\Account
     */
    public function retrieveConnectedAccount(string $accountId): \Stripe\Account
    {
        return $this->stripe->accounts->retrieve($accountId);
    }

    /**
     * Payouts
     */

    /**
     * Create a payout
     *
     * @param array $data
     * @return \Stripe\Payout
     */
    public function createPayout(array $data): \Stripe\Payout
    {
        // dd($data);
        return $this->stripe->payouts->create($data);
    }

    /**
     * Update a payout
     *
     * @param string $payoutId
     * @param array $data
     * @return \Stripe\Payout
     */
    public function updatePayout(string $payoutId, array $data): \Stripe\Payout
    {
        return $this->stripe->payouts->update($payoutId, $data);
    }

    /**
     * Retrieve a payout
     *
     * @param string $payoutId
     * @return \Stripe\Payout
     */
    public function retrievePayout(string $payoutId): \Stripe\Payout
    {
        return $this->stripe->payouts->retrieve($payoutId);
    }

    /**
     * List all payouts
     *
     * @param array $params
     * @return \Stripe\Collection
     */
    public function listAllPayouts(array $params = []): \Stripe\Collection
    {
        return $this->stripe->payouts->all($params);
    }

    /**
     * Cancel a payout
     *
     * @param string $payoutId
     * @return \Stripe\Payout
     */
    public function cancelPayout(string $payoutId): \Stripe\Payout
    {
        return $this->stripe->payouts->cancel($payoutId);
    }

    /**
     * Reverse a payout (requires creating a transfer reversal or refund)
     *
     * @param string $transferId
     * @param array $params
     * @return \Stripe\Transfer
     */
    public function reversePayout(string $transferId, array $params = [])
    {
        // return $this->stripe->transfers->reverse($transferId, $params);
    }


    // ###################################################################################################


    /**
     * Create a PaymentIntent
     *
     * @param array $data
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(array $data): \Stripe\PaymentIntent
    {
        return $this->stripe->paymentIntents->create($data);
    }

    /**
     * Update a PaymentIntent
     *
     * @param string $paymentIntentId
     * @param array $data
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function updatePaymentIntent(string $paymentIntentId, array $data): \Stripe\PaymentIntent
    {
        return $this->stripe->paymentIntents->update($paymentIntentId, $data);
    }

    /**
     * Retrieve a PaymentIntent
     *
     * @param string $paymentIntentId
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): \Stripe\PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * List all PaymentIntents
     *
     * @param array $params
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function listAllPaymentIntents(array $params = []): \Stripe\Collection
    {
        return $this->stripe->paymentIntents->all($params);
    }

    /**
     * Cancel a PaymentIntent
     *
     * @param string $paymentIntentId
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function cancelPaymentIntent(string $paymentIntentId): \Stripe\PaymentIntent
    {
        return $this->stripe->paymentIntents->cancel($paymentIntentId);
    }

    /**
     * Confirm a PaymentIntent
     *
     * @param string $paymentIntentId
     * @param array $params
     * @return \Stripe\PaymentIntent
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $params = []): \Stripe\PaymentIntent
    {
        return $this->stripe->paymentIntents->confirm($paymentIntentId, $params);
    }




    // ###################################################################################################



    /**
     * Create a refund
     *
     * @param array $data
     * @return \Stripe\Refund
     * @throws ApiErrorException
     */
    public function createRefund(array $data): \Stripe\Refund
    {
        return $this->stripe->refunds->create($data);
    }

    /**
     * Retrieve a refund
     *
     * @param string $refundId
     * @return \Stripe\Refund
     * @throws ApiErrorException
     */
    public function retrieveRefund(string $refundId): \Stripe\Refund
    {
        return $this->stripe->refunds->retrieve($refundId);
    }

    /**
     * List all refunds
     *
     * @param array $params
     * @return \Stripe\Collection
     * @throws ApiErrorException
     */
    public function listAllRefunds(array $params = []): \Stripe\Collection
    {
        return $this->stripe->refunds->all($params);
    }
}
