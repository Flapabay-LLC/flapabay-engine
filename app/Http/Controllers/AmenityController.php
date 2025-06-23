<?php



namespace App\Http\Controllers;



use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class AmenityController extends Controller
{
/**
* Display a listing of the resource.
*/
public function index()
{
try {
$amenities = Amenity::all();
return response()->json([
'status' => 'success',
'message' => 'Amenities fetched successfully',
'data' => $amenities
], 200);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Failed to fetch amenities',
'error' => $e->getMessage()
], 500);
}
}



/**
* Store a newly created resource in storage.
*/
public function store(Request $request)
{
$validator = Validator::make($request->all(), [
'name' => 'required|string',
'description' => 'nullable|string',
'white_icon' => 'nullable|string',
'black_icon' => 'nullable|string',
'svg' => 'nullable|string',
'uri' => 'nullable|string',
]);



if ($validator->fails()) {
return response()->json([
'status' => 'error',
'message' => 'Validation failed',
'errors' => $validator->errors()
], 422);
}



try {
$amenity = Amenity::create($validator->validated());



return response()->json([
'status' => 'success',
'message' => 'Amenity created successfully',
'data' => $amenity
], 201);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Failed to create amenity',
'error' => $e->getMessage()
], 500);
}
}



/**
* Display the specified resource.
*/
public function show(Amenity $amenity)
{
return response()->json([
'status' => 'success',
'data' => $amenity
], 200);
}



/**
* Update the specified resource in storage.
*/
public function update(Request $request, Amenity $amenity)
{
$validator = Validator::make($request->all(), [
'name' => 'sometimes|required|string',
'description' => 'nullable|string',
'white_icon' => 'nullable|string',
'black_icon' => 'nullable|string',
'svg' => 'nullable|string',
'uri' => 'nullable|string',
]);



if ($validator->fails()) {
return response()->json([
'status' => 'error',
'message' => 'Validation failed',
'errors' => $validator->errors()
], 422);
}



try {
$amenity->update($validator->validated());



return response()->json([
'status' => 'success',
'message' => 'Amenity updated successfully',
'data' => $amenity
], 200);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Failed to update amenity',
'error' => $e->getMessage()
], 500);
}
}



/**
* Remove the specified resource from storage.
*/
public function destroy(Amenity $amenity)
{
try {
$amenity->delete();



return response()->json([
'status' => 'success',
'message' => 'Amenity deleted successfully'
], 200);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Failed to delete amenity',
'error' => $e->getMessage()
], 500);
}
}
}
