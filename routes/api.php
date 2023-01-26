<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

Route::middleware( 'auth:sanctum' )->get( '/user', function ( Request $request ) {
    return $request->user();
} );

Route::controller( AuthController::class )->group( function () {
    Route::post( '/register', 'create' );
    Route::post( '/login', 'login' );
} );

Route::post( '/logout', [AuthController::class, 'logout'] );

//email verification route

Route::post( '/email/verification-notification', function ( Request $request ) {
    $request->user()->sendEmailVerificationNotification();

    return response( [
        'message' => 'Please, Check Your Email and Verify Your Account!',
    ] );
} )->middleware( ['auth:sanctum', 'throttle:6,1'] );

Route::get( '/email/verify/{id}/{hash}', function ( EmailVerificationRequest $request ) {
    $request->fulfill();

    return response( [
        'message' => 'Verification Successfull!',
    ] );
} )->middleware( ['auth:sanctum', 'signed'] )->name( 'verification.verify' );