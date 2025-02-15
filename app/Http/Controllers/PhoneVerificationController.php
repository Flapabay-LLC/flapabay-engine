<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PhoneVerificationController extends Controller
{
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->phone) {
            return response()->json(['error' => 'User phone number not registered'], 404);
        }

        return response()->json(['message' => 'Phone number found!', 'ok' => true], 200);
    }
}
