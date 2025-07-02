<?php

namespace App\Http\Controllers;

use App\Models\PropertyReview;
use App\Http\Requests\StorePropertyReviewRequest;
use App\Http\Requests\UpdatePropertyReviewRequest;

class PropertyReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = PropertyReview::with(['user', 'property'])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'All reviews fetched successfully',
            'data' => $reviews
        ]);
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
    public function store(StorePropertyReviewRequest $request)
    {
        $review = PropertyReview::create($request->all());
        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyReview $propertyReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyReview $propertyReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyReviewRequest $request)
    {
        $review = PropertyReview::findOrFail($request->input('property_review_id'));
        $review->update($request->only('rating', 'review'));

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyReview $propertyReview)
    {
        //
    }
}
