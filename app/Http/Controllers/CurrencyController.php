<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class CurrencyController extends Controller
{
    /**
     * Get all supported currencies.
     */
    public function getSupportedCurrencies(): JsonResponse
    {
        $currencies = Currency::where('is_active', true)->get(['code', 'name', 'symbol']);
        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

        /**
     * Store a newly created resource in storage.
     */
    public function setUserCurrency(Request $request): JsonResponse
    {
        $user = User::where('id', $request->input('user_id'))->first();

        // Update the currency
        $user->currency = $request->input('currency');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User currency updated successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $user = User::where('id', $request->input('user_id'))->first();

        // Update the currency
        $user->currency = $request->input('currency');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User currency updated successfully.',
            'data' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        //
    }
}
