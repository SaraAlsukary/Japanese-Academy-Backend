<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register',[AuthController::class,'register']);
Route::post('/verify-otp',[AuthController::class,'verifyOtp']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/resend-otp',[AuthController::class,'resendOtp']);

Route::middleware('auth:sanctum')->post('/logout',[AuthController::class,'logout']);
// forget password
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);



// profile
Route::get('/profile', [AuthController::class,'profile'])->middleware('auth:sanctum');
