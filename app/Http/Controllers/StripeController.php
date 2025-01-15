<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\MyStripe;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    use MyStripe;

    public function auth()
    {
        $isAuthenticated = $this->authenticate();
        return response()->json(['authenticated' => $isAuthenticated]);
    }

    public function connectedAccount(Request $request)
    {
        $data = $request->all();
        $account = $this->createConnectedAccount($data);
        return response()->json($account);
    }

    public function getConnectedAccount($accountId)
    {
        $account = $this->retrieveConnectedAccount($accountId);
        return response()->json($account);
    }

    public function payout(Request $request)
    {
        $data = $request->all();
        $payout = $this->createPayout($data);
        return response()->json($payout);
    }

    public function getPayout($payoutId)
    {
        $payout = $this->retrievePayout($payoutId);
        return response()->json($payout);
    }

    public function getPayouts(Request $request)
    {
        $params = $request->all();
        $payouts = $this->listAllPayouts($params);
        return response()->json($payouts);
    }

    public function xPayout($payoutId)
    {
        $payout = $this->cancelPayout($payoutId);
        return response()->json($payout);
    }

    public function undoPayout($payoutId, Request $request)
    {
        $params = $request->all();
        $refund = $this->reversePayout($payoutId, $params);
        return response()->json($refund);
    }

    // -----------------------------------------------------------------

    public function makePaymentIntent(Request $request)
    {
        try {
            $data = $request->all();
            $paymentIntent = $this->createPaymentIntent($data);
            return response()->json($paymentIntent);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function editPaymentIntent($id, Request $request)
    {
        try {
            $data = $request->all();
            $paymentIntent = $this->updatePaymentIntent($id, $data);
            return response()->json($paymentIntent);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getPaymentIntent($id)
    {
        try {
            $paymentIntent = $this->retrievePaymentIntent($id);
            return response()->json($paymentIntent);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function allPaymentIntents(Request $request)
    {
        try {
            $params = $request->all();
            $paymentIntents = $this->listAllPaymentIntents($params);
            return response()->json($paymentIntents);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function xPaymentIntent($id)
    {
        try {
            $paymentIntent = $this->cancelPaymentIntent($id);
            return response()->json($paymentIntent);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function confirmedPaymentIntent($id, Request $request)
    {
        try {
            $params = $request->all();
            $paymentIntent = $this->confirmPaymentIntent($id, $params);
            return response()->json($paymentIntent);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // ----------------------------------------------------------------------------------

    public function makeRefund(Request $request)
    {
        try {
            $data = $request->all();
            $refund = $this->createRefund($data);
            return response()->json($refund);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getRefund($id)
    {
        try {
            $refund = $this->retrieveRefund($id);
            return response()->json($refund);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function allRefunds(Request $request)
    {
        try {
            $params = $request->all();
            $refunds = $this->listAllRefunds($params);
            return response()->json($refunds);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
