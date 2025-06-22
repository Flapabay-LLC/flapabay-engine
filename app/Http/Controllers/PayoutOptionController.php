<?php

namespace App\Http\Controllers;

use App\Models\PayoutOption;
use Illuminate\Http\Request;

class PayoutOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $options = PayoutOption::all();

        return response()->json([
            'success' => true,
            'message' => 'Payout options fetched successfully',
            'data' => $options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'icon_alt' => 'nullable|string',
            'currency' => 'required|string|max:10',
        ]);

        $option = PayoutOption::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payout option created successfully',
            'data' => $option
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayoutOption $payoutOption)
    {
        return response()->json([
            'success' => true,
            'data' => $payoutOption
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayoutOption $payoutOption)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'icon_alt' => 'nullable|string',
            'currency' => 'sometimes|string|max:10',
        ]);

        $payoutOption->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payout option updated successfully',
            'data' => $payoutOption
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayoutOption $payoutOption)
    {
        $payoutOption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payout option deleted successfully'
        ]);
    }
}
