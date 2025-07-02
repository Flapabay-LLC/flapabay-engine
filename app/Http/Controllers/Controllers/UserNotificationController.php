<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // $request->validate([
        //     'user_id' => 'required',
        //     'title' => 'required|string',
        //     'message' => 'required|string',
        //     'sender_user_id' => 'required',
        //     'property_id' => 'required',
        // ]);

        // dd($request);
        try {
            $user = User::find($request->user_id);
            $user->notify(new UserNotification([
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'from_user_id' => $request->sender_user_id,
                'property_id' => $request->property_id,
                'textColor' => $request->textColor,
                'bgColor' => $request->bgColor,
                'icon' => $request->icon,
                'icon_alt' => $request->icon_alt,
            ]));

            return response()->json(['message' => 'Notification created successfully']);
        } catch (\Throwable $th) {
            return response()->json(['error'=> 'failure', 'message' => $th->getMessage()], 500);
        }
    }

    public function fetchUserNotifications($userId)
    {
        try {
            $user = User::findOrFail($userId);
            return response()->json($user->notifications);
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th->getMessage()], 500);
        }
    }

    public function deleteUserNotification($userId, $notificationId)
    {
        $user = User::findOrFail($userId);
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    public function deleteUserAllNotifications($userId)
    {
        $user = User::findOrFail($userId);
        $user->notifications()->delete();

        return response()->json(['message' => 'All notifications deleted successfully']);
    }

}
