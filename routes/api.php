<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


//! Auth Routes
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');
    Route::post('password_change', 'Api\AuthController@change_password');
    Route::post('forgot_password', 'Api\AuthController@forgot_password');
});

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::get('logout', 'Api\AuthController@logout');
    Route::get('user', 'Api\AuthController@user');

});


Route::group([
    'prefix' => 'profile',
    'middleware' => 'auth:api'
], function () {
    Route::post('update', 'Api\User\ProfileController@update_profile');
});

Route::group([
    'prefix' => 'admin',
    'middleware' => 'auth:api'
], function () {
    Route::post('send-invitations', 'Api\Admin\InvitationController@send_invitations');
});
