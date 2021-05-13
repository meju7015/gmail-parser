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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::prefix('/v1')->group(function () {

    Route::get('/todos/{userId}', 'App\Http\Controllers\TodoApiController@list');

    Route::post('/todos/{userId}', 'App\Http\Controllers\TodoApiController@store');

    Route::patch('/todos/{id}', 'App\Http\Controllers\TodoApiController@update');

    Route::delete('/todos/{id}', 'App\Http\Controllers\TodoApiController@delete');

});
