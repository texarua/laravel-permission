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

Route::group(['namespace' => 'App\Http\Controllers\API'], function() {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login')->name('login');
    Route::group(['middleware' => ['auth:api']],function () {
        Route::get('/logout', 'AuthController@logout');
        Route::post('/2fa/verify', 'AuthController@check2Fa');
        Route::get('/2fa/resend', 'AuthController@resend2Fa');
        Route::group(['middleware' => 'twofactor'], function() {
            Route::get('/products', 'ProductController@index');
        });
    });
});

