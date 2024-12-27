<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class HostController extends Controller
{
    public function getHostInfo($host_id): JsonResponse
    {
        // Fetch host information by ID
        $host = User::with('details')->where('host_id',$host_id)->first();

        // Check if the host exists
        if (!$host) {
            return response()->json([
                'status' => 'error',
                'message' => 'Host not found'
            ], 404);
        }

        // Return host information
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $host->id,
                'name' => $host->fname . ' ' . $host->lname,
                'email' => $host->email,
                'phone' => $host->phone,
                'properties' => $host->properties, // Assuming a relationship exists
            ]
        ]);
    }
}
