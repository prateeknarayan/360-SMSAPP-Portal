<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\WebhookController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'Api'], function () {
    Route::post('clients',  [ClientController::class, 'storeClient']);
    Route::get('clients/{client}',  [ClientController::class, 'getClient']);
    Route::post('twilio-request',  [ClientController::class, 'twilioRequest']);
});

Route::post('smsapp/{platform}.php', [App\Http\Controllers\WebhookController::class, 'webhook']);