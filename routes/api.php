<?php

use App\Http\Controllers\PanicController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');


// create user
Route::post('users/create', 'ApiController@create');

// update user
Route::post('users/update', 'ApiController@update');

// login user
Route::post('users/login', 'ApiController@login');

// upload file
Route::post('users/upload_file', 'ApiController@uploadFile');

// delete File
Route::post('users/delete_file', 'ApiController@deleteFile');

// upload upload Personal File
Route::post('users/upload_personal_file', 'ApiController@uploadPersonalFile');

// get polizas
Route::post('polizas/get', 'ApiController@getPolizas');

// get user forgot password
Route::post('users/forgot_password', 'ApiController@forgotPassword');
Route::post('/panic/destructive', 'PanicController@triggerDestructive');
Route::get('/panic/status', 'PanicController@status');

// get user profile
// Route::post('users/profile', 'ApiController@profile');


// get ticket price
// Route::post('tickets/get_price', 'ApiController@getPrice');

// set ticket
// Route::post('tickets/set', 'ApiController@setTicket');

// get buses
// Route::post('buses/list', 'ApiController@getBuses');

// get bus stops
// Route::post('bus_stops/list', 'ApiController@getBusStops');

// get valid sentence
// Route::post('valid_sentence/get', 'ApiController@getValidSentence');

// set tracking
// Route::post('tracking/set', 'ApiController@setTracking');

// get tracking
// Route::post('tracking/get', 'ApiController@getTracking');


