<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Settings\AuthenticationSettingsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/branch', 'App\Http\Controllers\Company\BranchController@branch');

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('backend-login', [AuthenticationController::class, 'backendLogin']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::post('/register', [AuthenticationController::class, 'register']);


Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/create-user', [AuthenticationController::class, 'createUser'])->middleware('auth:sanctum,', 'role'); // middleware that are to be used are separated by a comma                                // \/ This is the middleware that is to be used
// Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware(RoleAuthentication::class);
Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware('auth:sanctum');


// Super Admin Routes
Route::get('/setting/auth-attempts', [AuthenticationSettingsController::class, 'authAttempts'])->middleware('auth:sanctum', 'role:super-admin');
Route::get('/setting/auth-attempts/{id}', [AuthenticationSettingsController::class, 'getOneAttempt'])->middleware('auth:sanctum', 'role:super-admin');
Route::patch('/setting/auth-attempts/{id}', [AuthenticationSettingsController::class, 'updateAttempts'])->middleware('auth:sanctum', 'role:super-admin');
