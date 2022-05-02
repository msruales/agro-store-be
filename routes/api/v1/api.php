<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
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


Route::middleware('auth:api')->get('/user', function (Request $request) {

    return $request->user();

});

Route::group(['middleware' => 'auth:api', 'prefix' => 'dashboard'], function(){

    Route::get('/user', [AuthController::class, 'get_user']);

    Route::apiResources([
        'products' => \App\Http\Controllers\Api\v1\Dashboard\ProductController::class,
        'categories' => \App\Http\Controllers\Api\v1\Dashboard\CategoryController::class,
    ]);
    Route::post('categories/store_image/{category}', [\App\Http\Controllers\Api\v1\Dashboard\CategoryController::class, 'store_image']);

});


Route::group(['namespace' => 'App\Http\Controllers\Api\v1\Auth'], function(){

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

});
