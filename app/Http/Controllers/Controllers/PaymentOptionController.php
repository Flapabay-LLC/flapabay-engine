<?php

namespace App\Http\Controllers;

use App\Models\PaymentOption;
use App\Http\Requests\StorePaymentOptionRequest;
use App\Http\Requests\UpdatePaymentOptionRequest;

class PaymentOptionController extends Controller
{
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
    public function store(StorePaymentOptionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentOption $paymentOption)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentOption $paymentOption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentOptionRequest $request, PaymentOption $paymentOption)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentOption $paymentOption)
    {
        //
    }
}
