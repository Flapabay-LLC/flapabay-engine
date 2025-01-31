<?php

namespace App\Http\Controllers;

use App\Models\Stay;
use App\Http\Requests\StoreStayRequest;
use App\Http\Requests\UpdateStayRequest;
use App\Traits\MyPicture;
use App\Traits\MyVideo;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\JsonResponse;

class StayController extends Controller
{
    use MyPicture, MyVideo;

    public function index()
    {
        return response()->json(Stay::with(['user', 'property'])->paginate(10));
    }
    public function host($host_id)
    {
        return response()->json(Stay::where('host_id', $host_id)->with(['user', 'property'])->paginate(10));
    }


    public function store(StoreStayRequest $request): JsonResponse
    {
        // Upload images and videos
        $imagePaths = $this->uploadImagesToWasabi($request);
        $videoPaths = $this->uploadVideosToWasabi($request);

        // Create Stay
        $stay = Stay::create([
            'user_id' => 1,
            'host_id' => $request->host_id,
            'property_id' => $request->property_id,
            'title' => $request->title,
            'description' => $request->description,
            'about_this_place' => $request->about_this_place,
            'starting' => $request->starting,
            'ending' => $request->ending,
            'max_guests' => $request->max_guests,
            'total_nights' => $request->total_nights,
            'price_per_night' => $request->price_per_night,
            'total_price' => $request->total_price,
            'amenities' => $request->amenities,
            'images' => json_encode($imagePaths),
            'videos' => json_encode($videoPaths),
            'is_available' => (int) $request->is_available,
        ]);

        return response()->json(['message' => 'Stay created successfully!', 'stay' => $stay], 201);
    }

    public function show(Stay $stay)
    {
        return response()->json($stay->load(['user', 'property']));
    }

    public function update(UpdateStayRequest $request, Stay $stay)
    {
        $stay->update($request->validated());
        return response()->json(['message' => 'Stay updated successfully!', 'stay' => $stay]);
    }

    public function destroy(Stay $stay)
    {
        $stay->delete();
        return response()->json(['message' => 'Stay deleted successfully!']);
    }
}
