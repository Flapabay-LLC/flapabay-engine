<?php

namespace App\Http\Controllers;

use App\Models\listingReview;
use App\Http\Requests\StorelistingReviewRequest;
use App\Http\Requests\UpdatelistingReviewRequest;

class listingReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = listingReview::with(['user', 'listing'])->orderBy('created_at', 'desc')->get();
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
    public function store(StorelistingReviewRequest $request)
    {
        $review = listingReview::create($request->all());
        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(listingReview $listingReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(listingReview $listingReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatelistingReviewRequest $request)
    {
        $review = listingReview::findOrFail($request->input('listing_review_id'));
        $review->update($request->only('rating', 'review'));

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(listingReview $listingReview)
    {
        //
    }
}
