<?php

namespace App\Traits;

use Carbon\Carbon;

trait AuthTrait
{
    // protected $auth;

    // public function __construct()
    // {

    // }

    public function generate_otp($user, $request){
        // Step 4: Generate the OTP
        $otp = rand(10000, 99999);
        // Step 5: Store the OTP and expiration time
        $expiresAt = Carbon::now()->addMinutes(5);
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();
        return $otp;
    }


}