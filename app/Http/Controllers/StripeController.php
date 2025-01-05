<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\MyStripe;

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
}
