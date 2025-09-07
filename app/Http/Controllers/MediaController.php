<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Property;

class MediaController extends Controller
{
    /**
     * Initialize presigned media upload
     */
    public function initializeUpload(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'file_name' => 'required|string|max:255',
                'file_size' => 'required|integer|min:1|max:10485760', // 10MB max
                'file_type' => 'required|string|in:image/jpeg,image/png,image/jpg,image/webp',
                'property_id' => 'nullable|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Invalid upload parameters',
                    'field_errors' => $validator->errors()
                ], 422);
            }

            // Generate unique file key
            $fileExtension = pathinfo($request->file_name, PATHINFO_EXTENSION);
            $uniqueFileName = Str::uuid() . '.' . $fileExtension;
            $fileKey = 'uploads/' . $user->id . '/' . date('Y/m/d') . '/' . $uniqueFileName;

            // Check if using S3 or local storage
            $disk = config('filesystems.default');
            
            if ($disk === 's3') {
                // Generate presigned URL for S3
                $presignedUrl = Storage::disk('s3')->temporaryUrl(
                    $fileKey,
                    now()->addMinutes(15), // 15 minutes expiry
                    [
                        'ResponseContentType' => $request->file_type,
                        'ResponseContentDisposition' => 'attachment; filename="' . $request->file_name . '"'
                    ]
                );

                return response()->json([
                    'code' => 'SUCCESS',
                    'message' => 'Upload URL generated successfully',
                    'upload_url' => $presignedUrl,
                    'file_key' => $fileKey,
                    'expires_at' => now()->addMinutes(15)->toISOString(),
                    'method' => 'PUT',
                    'headers' => [
                        'Content-Type' => $request->file_type
                    ]
                ]);
            } else {
                // For local storage, return endpoint for direct upload
                return response()->json([
                    'code' => 'SUCCESS',
                    'message' => 'Upload endpoint ready',
                    'upload_url' => url('/api/v1/media/uploads/direct'),
                    'file_key' => $fileKey,
                    'expires_at' => now()->addMinutes(15)->toISOString(),
                    'method' => 'POST',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $request->bearerToken(),
                        'Accept' => 'application/json'
                    ],
                    'form_data' => [
                        'file_key' => $fileKey,
                        'property_id' => $request->property_id
                    ]
                ]);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Direct upload for local storage
     */
    public function directUpload(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240', // 10MB
                'file_key' => 'required|string',
                'property_id' => 'nullable|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Invalid file upload',
                    'field_errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $fileKey = $request->file_key;

            // Store file using the provided key
            $path = Storage::disk('public')->putFileAs(
                dirname($fileKey),
                $file,
                basename($fileKey)
            );

            if (!$path) {
                return response()->json([
                    'code' => 'UPLOAD_FAILED',
                    'message' => 'Failed to store file'
                ], 500);
            }

            $fileUrl = Storage::disk('public')->url($path);

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'File uploaded successfully',
                'file_key' => $fileKey,
                'file_url' => $fileUrl,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType()
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Attach media to property
     */
    public function attachToProperty(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'property_id' => 'required|exists:properties,id',
                'file_keys' => 'required|array|min:1|max:20',
                'file_keys.*' => 'required|string',
                'action' => 'required|in:add,replace,remove'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 'VALIDATION_FAILED',
                    'message' => 'Invalid attachment parameters',
                    'field_errors' => $validator->errors()
                ], 422);
            }

            $property = Property::where('host_id', $user->id)
                ->where('id', $request->property_id)
                ->first();

            if (!$property) {
                return response()->json([
                    'code' => 'PROPERTY_NOT_FOUND',
                    'message' => 'Property not found'
                ], 404);
            }

            $currentImages = $property->images ? json_decode($property->images, true) : [];
            $newFileKeys = $request->file_keys;

            switch ($request->action) {
                case 'add':
                    $updatedImages = array_merge($currentImages, $newFileKeys);
                    break;
                case 'replace':
                    $updatedImages = $newFileKeys;
                    break;
                case 'remove':
                    $updatedImages = array_diff($currentImages, $newFileKeys);
                    break;
            }

            // Remove duplicates and reindex
            $updatedImages = array_values(array_unique($updatedImages));

            $property->images = json_encode($updatedImages);
            $property->save();

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Media attached successfully',
                'property_id' => $property->id,
                'images' => $updatedImages,
                'total_images' => count($updatedImages)
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get media upload status
     */
    public function getUploadStatus(Request $request, $fileKey)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if file exists in storage
            $disk = config('filesystems.default');
            $exists = Storage::disk($disk === 's3' ? 's3' : 'public')->exists($fileKey);

            if ($exists) {
                $fileUrl = $disk === 's3' 
                    ? Storage::disk('s3')->url($fileKey)
                    : Storage::disk('public')->url($fileKey);

                return response()->json([
                    'code' => 'SUCCESS',
                    'message' => 'File found',
                    'file_key' => $fileKey,
                    'file_url' => $fileUrl,
                    'status' => 'uploaded'
                ]);
            } else {
                return response()->json([
                    'code' => 'FILE_NOT_FOUND',
                    'message' => 'File not found',
                    'file_key' => $fileKey,
                    'status' => 'not_found'
                ], 404);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}