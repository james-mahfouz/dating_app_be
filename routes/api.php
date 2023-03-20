<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(["prefix"=>"v0.0.1"], function(){
    Route::group(["prefix"=>"users"], function(){
        Route::get('/login', [AuthController::class, "login"]);
        Route::post('/register', [AuthController::class, "register"]);
        Route::get('/logout', [AuthController::class, "logout"]);
        Route::post('/change', [AuthController::class, "change_infos"]);
        Route::post('/getusers', [AuthController::class, "get_users"]);
        Route::post('/upload_picture', [AuthController::class, "upload_picture"]);
        Route::post('block/{user}', [AuthController::class, "block"]);
        Route::post('favorite/{user}', [AuthController::class, "favorite"]);
    });
});
