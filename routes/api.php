<?php

use App\Http\Controllers\AmenityController;
use App\Http\Controllers\Auth\AuthenticatorController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReviewController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ComsmeticController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PropertyReviewController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlaceItemController;
use App\Http\Controllers\PropertyTypeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CoHostController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return response()->json(['version' => app()->version()]);
});

//Open Routes
Route::prefix('v1')->group(function () {
    Route::get('/testing', [UserController::class, 'test']);
    // Authentication routes
    // Route::post('register', [AuthenticatorController::class, 'register']);
    Route::post('register-user-details', [AuthenticatorController::class, 'registerUserDetails']);
    Route::post('get-email-phone-otp', [AuthenticatorController::class, 'getEmailPhoneOtp']);
    Route::post('login-with-otp', [AuthenticatorController::class, 'otpLogin']);
    Route::post('login', [AuthenticatorController::class, 'login']);
    Route::post('login-w-phone', [AuthenticatorController::class, 'loginWPhone']);
    Route::post('get-email-otp', [AuthenticatorController::class, 'getEmailOtp']);
    Route::post('get-phone-otp', [AuthenticatorController::class, 'getPhoneOtp']); //step1

    Route::post('verify-otp-byphone', [AuthenticatorController::class, 'verifyOtpByPhone']);
    Route::post('verify-otp-byemail', [AuthenticatorController::class, 'verifyOtpByEmail']);
    Route::post('logout', [AuthenticatorController::class, 'logout']);

    Route::post('reset-password', [AuthenticatorController::class, 'resetPassword']);
    Route::post('forgot-password', [AuthenticatorController::class, 'forgotPassword']);

    // Property routes
    Route::get('properties', [PropertyController::class, 'getProperties']);

    //Google & Facebook Auth
    // Route::post('google/signup', [GoogleAuthController::class, 'googleSignUp']);
    Route::post('google/signin', [GoogleAuthController::class, 'googleSignIn']);
    // Route::post('google/callback', [GoogleAuthController::class, 'googleCallback']);


    Route::post('facebook/signin', [FacebookController::class, 'facebookSignIn']);
    // Route::get('facebook/callback', [FacebookController::class, 'handleFacebookCallback']);
    // Category routes
    Route::post('categories/add', [CategoryController::class, 'addCategory']);
    Route::get('categories', [CategoryController::class, 'getAllCategories']);


    //Supported Languages
    Route::get('/supported-lang', [LanguageController::class, 'getSupportedLang']);
    
    Route::get('system/amenities', [ListingController::class, 'getSystemAmenities']);
    Route::get('system/favorites', [ListingController::class, 'getSystemFavorites']);
    Route::get('system/place-items', [ListingController::class, 'getSystemPlaceItems']);
    Route::get('system/property-types', [ListingController::class, 'getSystemPropertyTypes']);
});

// Protected routes with JWT api authentication
Route::middleware('auth:api')->prefix('v1')->group(function () {

    // User routes
    Route::get('users/{user_id}', [UserController::class, 'show']);
    Route::post('users/{user_id}', [UserController::class, 'update']);
    Route::post('users/{user_id}/profile-picture', [UserController::class, 'updateProfilePicture']);
    Route::post('users/{user_id}/complete-details', [UserController::class, 'completeUserDetails']);
    Route::get('users/{user_id}/reviews', [UserReviewController::class, 'userReview']);

    // Favorites routes
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::get('favorites/user/{userId}', [FavoriteController::class, 'getUserFavorites']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites', [FavoriteController::class, 'destroy']);

    // Chat routes
    Route::get('chats', [ChatController::class, 'getAllMyChats']);
    Route::get('chats/unread', [ChatController::class, 'getAllMyUnreadMessages']);
    Route::get('chats/read', [ChatController::class, 'getAllMyReadMessages']);
    Route::get('chats/{chatId}/messages', [ChatController::class, 'getAllChatMessages']);
    Route::post('chats/message', [ChatController::class, 'sendChatMessage']);
    Route::post('chats/reply', [ChatController::class, 'sendMessageThreadReply']);
    Route::delete('chats/message/{messageId}/me', [ChatController::class, 'deleteMessageForMe']);
    Route::delete('chats/message/{messageId}/both', [ChatController::class, 'deleteMessageForBoth']);

    // Listing routes
    Route::get('listings/search', [ListingController::class, 'searchListings']);
    Route::post('listings', [ListingController::class, 'createNewListing']);
    Route::post('listings/{listingId}', [ListingController::class, 'updateHostListing']);
    Route::get('listings/host', [ListingController::class, 'fetchHostListings']);
    Route::delete('listings/{listingId}', [ListingController::class, 'deleteHostListing']);

    // Property routes
    // Route::get('properties', [PropertyController::class, 'getProperties']);
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
    Route::post('search', [ListingController::class, 'search']);

    // Property Rating & Reviews
    Route::get('/reviews', [PropertyReviewController::class, 'index']);
    Route::post('/create-review', [PropertyReviewController::class, 'store']);
    Route::post('/update-review', [PropertyReviewController::class, 'update']);

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

    //Supported Languages
    Route::post('/supported-lang', [LanguageController::class, 'addSupportedLang']);
    Route::post('/set-user-default-supported-lang', [LanguageController::class, 'setUserDefaultLang']);
    Route::get('/translations', [LanguageController::class, 'getTranslationsWithPluralization']);

    //User Notifications
    Route::post('/create-notification', [UserNotificationController::class, 'store']);
    Route::get('/fetch-user-notifications/{userId}', [UserNotificationController::class, 'fetchUserNotifications']);
    Route::delete('/delete-user-notification/{userId}/{notificationId}', [UserNotificationController::class, 'deleteUserNotification']);
    Route::delete('/delete-user-all-notifications/{userId}', [UserNotificationController::class, 'deleteUserAllNotifications']);

    //Currencies
    Route::get('/get-supported-currencies', [CurrencyController::class, 'getSupportedCurrencies']);
    Route::post('/set-user-currency', [CurrencyController::class, 'setUserCurrency']);

    //Cosmetics
    Route::get('icons', [ComsmeticController::class, 'getIcons']);
    Route::post('icons', [ComsmeticController::class, 'createIcon']);

    //Locations
    Route::get('locations', [LocationController::class, 'getLocations']);
    Route::post('location', [LocationController::class, 'createLocation']);

    // System Data Management Routes
    Route::post('/system/amenities', [ListingController::class, 'createSystemAmenity']);
    Route::post('/system/favorites', [ListingController::class, 'createSystemFavorite']);
    Route::post('/system/property-types', [ListingController::class, 'createSystemPropertyType']);

    // Reservation routes
    Route::post('reservations', [ReservationController::class, 'create']);
    Route::get('reservations', [ReservationController::class, 'index']);
    Route::get('reservations/{id}', [ReservationController::class, 'show']);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);

    // Co-host routes
    Route::post('co-hosts/whitelist', [CoHostController::class, 'addPropertyToWhitelist']);
    Route::post('co-hosts/signup', [CoHostController::class, 'signUpAsCoHost']);
    Route::get('co-hosts/properties', [CoHostController::class, 'getPropertiesManagedByCoHost']);
    Route::get('co-hosts/members', [CoHostController::class, 'getHostCoHostMembers']);

    // Support routes
    Route::post('support/tickets', [SupportController::class, 'submitSupportTicket']);
    Route::get('support/tickets', [SupportController::class, 'viewSupportTickets']);
    Route::get('support/ticket/{ticketId}', [SupportController::class, 'getTicketDetails']);
    Route::post('support/ticket/{ticketId}/responses', [SupportController::class, 'addTicketResponse']);
    Route::get('support/faqs', [SupportController::class, 'fetchFaqs']);

});


