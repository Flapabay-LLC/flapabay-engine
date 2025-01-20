<?php

use App\Http\Controllers\Auth\AuthenticatorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReviewController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return response()->json(['version' => app()->version()]);
});

//Open Routes
Route::prefix('v1')->group(function () {
    Route::get('/testing', [UserController::class, 'test']);
    // Authentication routes
    Route::post('register', [AuthenticatorController::class, 'register']);
    Route::post('login', [AuthenticatorController::class, 'login']);
    Route::post('get-email-otp', [AuthenticatorController::class, 'getEmailOtp']);
    Route::post('get-phone-otp', [AuthenticatorController::class, 'getPhoneOtp']);
    Route::get('verify-otp', [AuthenticatorController::class, 'verifyOtp']);
    Route::post('logout', [AuthenticatorController::class, 'logout']);
    Route::post('reset-password', [AuthenticatorController::class, 'resetPassword']);
    Route::post('forgot-password', [AuthenticatorController::class, 'forgotPassword']);


    // Category routes
    Route::post('categories/add', [CategoryController::class, 'addCategory']);
    Route::get('categories', [CategoryController::class, 'getAllCategories']);
});

// Protected routes with JWT api authentication
Route::middleware('auth:api')->prefix('v1')->group(function () {

    // User routes
    Route::get('users/{user_id}', [UserController::class, 'show']);
    Route::put('users/{user_id}', [UserController::class, 'update']);
    Route::post('users/{user_id}/profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::get('users/{user_id}/reviews', [UserReviewController::class, 'userReview']);



    // Property routes
    Route::get('properties', [PropertyController::class, 'getProperties']);
    Route::post('properties', [PropertyController::class, 'createProperties']);
    Route::post('update-properties', [PropertyController::class, 'updateProperties']);
    Route::get('properties/{propertyId}', [PropertyController::class, 'getProperty']);
    Route::delete('properties/{propertyId}', [PropertyController::class, 'deleteProperty']);
    Route::get('properties/{propertyId}/reviews', [PropertyController::class, 'getPropertyReviews']);
    Route::get('properties/{propertyId}/description', [PropertyController::class, 'getPropertyDescription']);
    Route::get('properties/{propertyId}/price-details', [PropertyController::class, 'getPropertyPriceDetails']);
    Route::get('properties/{propertyId}/amenities', [PropertyController::class, 'getPropertyAmenities']);
    Route::get('properties/{propertyId}/availability', [PropertyController::class, 'getAvailabilityDates']);


    // Property search filtering routes
    Route::post('filter-listings', [ListingController::class, 'search']);

    // Booking routes
    Route::post('booking', [BookingController::class, 'createBooking']);
    Route::get('bookings', [BookingController::class, 'getBookings']);
    Route::get('booking/{book_id}', [BookingController::class, 'getBooking']);
    Route::put('booking/{book_id}/cancel', [BookingController::class, 'cancelBooking']);
    Route::post('bookings/{booking_id}/invoice', [BookingController::class, 'generateInvoice']);

    // Host routes
    Route::get('bookings/host/{host_id}', [HostController::class, 'getHostInfo']);
    Route::post('host/signup', [UserController::class, 'registerHost']);

    // Payment Payout routes
    Route::post('payments/checkout', [PaymentController::class, 'checkout']);
    Route::get('payments/status', [PaymentController::class, 'status']);
    Route::get('payments/payout-options', [PaymentController::class, 'options']);
    Route::post('payments/create-payout-options', [PaymentController::class, 'addOption']);
    Route::post('payments/update-payout-options/{id}', [PaymentController::class, 'editOption']);

    //Payment User Payment Details routes
    Route::get('payments/user-payment-details', [PaymentController::class, 'getUserPaymentDetails']);
    Route::post('payments/user-payment-details', [PaymentController::class, 'addUserPaymentDetails']);
    Route::post('payments/user-payment-details/edit/{id}', [PaymentController::class, 'editUserPaymentDetails']);

    // Stripe routes
    Route::get('/stripe/authenticate', [StripeController::class, 'auth']);
    Route::post('/stripe/account', [StripeController::class, 'connectedAccount']);
    Route::get('/stripe/account/{accountId}', [StripeController::class, 'getConnectedAccount']);
    Route::post('/stripe/payout', [StripeController::class, 'payout']);
    Route::get('/stripe/payout/{payoutId}', [StripeController::class, 'getPayout']);
    Route::get('/stripe/payouts', [StripeController::class, 'getPayouts']);
    Route::post('/stripe/payout/cancel/{payoutId}', [StripeController::class, 'xPayout']);
    Route::post('/stripe/payout/reverse/{payoutId}', [StripeController::class, 'undoPayout']);

    // Payment Intent routes
    Route::post('/stripe/payment-intent/create', [StripeController::class, 'makePaymentIntent']);
    Route::post('/stripe/payment-intent/update/{id}', [StripeController::class, 'modifyPaymentIntent']);
    Route::get('/stripe/payment-intent/retrieve/{id}', [StripeController::class, 'getPaymentIntent']);
    Route::get('/stripe/payment-intents', [StripeController::class, 'allPaymentIntents']);
    Route::post('/stripe/payment-intent/cancel/{id}', [StripeController::class, 'xPaymentIntent']);
    Route::post('/stripe/payment-intent/confirm/{id}', [StripeController::class, 'confirmedPaymentIntent']);
    Route::post('/stripe/refund/create', [StripeController::class, 'makeRefund']);
    Route::get('/stripe/refund/retrieve/{id}', [StripeController::class, 'getRefund']);
    Route::get('/stripe/refunds', [StripeController::class, 'allRefunds']);

    //User Notifications
    Route::post('/create-notification', [UserNotificationController::class, 'store']);
    Route::get('/fetch-user-notifications/{userId}', [UserNotificationController::class, 'fetchUserNotifications']);
    Route::delete('/delete-user-notification/{userId}/{notificationId}', [UserNotificationController::class, 'deleteUserNotification']);
    Route::delete('/delete-user-all-notifications/{userId}', [UserNotificationController::class, 'deleteUserAllNotifications']);

});
