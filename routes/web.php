<?php

// use App\Http\Controllers\AuthController;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\UserReviewController;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Test route
$router->get('/test', 'UserController@test');
// $router->get('/users/{user_id}/reviews', 'UserReviewController@index');

// Api version 1
$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('get-otp', 'AuthController@getOtp');
    $router->get('verify-otp', 'AuthController@verifyOtp');
    $router->post('logout','AuthController@logout');
    $router->post('reset-password', 'AuthController@resetPassword');
    $router->post('forgot-password', 'AuthController@forgotPassword');

    // Profile routes
    $router->get('users/{user_id}', 'UserController@show');
    $router->put('/users/{user_id}', 'UserController@update');
    $router->post('/users/{user_id}/profile-picture', 'UserController@updateProfilePicture');
    $router->get('/users/{user_id}/reviews', 'UserReviewController@userReview');

    $router->get('properties', 'PropertyController@getProperties');
    $router->post('properties', 'PropertyController@createProperties');
    $router->post('update-properties', 'PropertyController@updateProperties');
    $router->get('properties/{propertyId}', 'PropertyController@getProperty');
    $router->delete('properties/{propertyId}', 'PropertyController@deleteProperty');
    $router->get('properties/{propertyId}/reviews', 'PropertyController@getPropertyReviews');

    $router->get('properties/{propertyId}/description', 'PropertyController@getPropertyDescription');
    $router->get('properties/{propertyId}/price-details', 'PropertyController@getPropertyPriceDetails');
    $router->get('properties/{propertyId}/amenities', 'PropertyController@getPropertyAmenities');
    $router->get('properties/{propertyId}/availability', 'PropertyController@getAvailabilityDates');


    $router->post('booking', 'BookingController@createBooking');
    $router->get('bookings', 'BookingController@getBookings');
    $router->get('booking/{book_id}', 'BookingController@getBooking');
    $router->put('booking/{book_id}/cancel', 'BookingController@cancelbooking');

    $router->get('bookings/host/{host_id}', 'HostController@getHostInfo');

    $router->post('payments/checkout', 'PaymentController@checkout');
    $router->get('payments/status', 'PaymentController@status');
    $router->get('payments/options', 'PaymentController@options');
    $router->post('payments/options/add', 'PaymentController@addOption');
    $router->put('payments/options/edit/{id}', 'PaymentController@editOption');

    $router->get('/stripe/authenticate', 'StripeController@auth');
    $router->post('/stripe/account', 'StripeController@connectedAccount');
    $router->get('/stripe/account/{accountId}', 'StripeController@getConnectedAccount');
    $router->post('/stripe/payout', 'StripeController@payout');
    $router->get('/stripe/payout/{payoutId}', 'StripeController@getPayout');
    $router->get('/stripe/payouts', 'StripeController@getPayouts');
    $router->post('/stripe/payout/cancel/{payoutId}', 'StripeController@xPayout');
    $router->post('/stripe/payout/reverse/{payoutId}', 'StripeController@undoPayout');




    $router->post('categories/add', 'CategoryController@addCategory');
    $router->get('categories', 'CategoryController@getAllCategories');

    $router->post('host/signup', 'UserController@registerHost');
    $router->post('host/signup', 'UserController@registerHost');

    $router->post('bookings/{booking_id}/invoice', 'BookingController@generateInvoice');

});

// Protected routes
$router->group(['middleware' => 'auth:sanctum'], function () use ($router) {
    $router->get('profile', 'UserController@profile');
});

