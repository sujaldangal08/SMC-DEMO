<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/branch', 'App\Http\Controllers\Company\BranchController@branch');
Route::post('/branch', 'App\Http\Controllers\Company\BranchController@createBranch');
Route::patch('/branch/{id}', 'App\Http\Controllers\Company\BranchController@updateBranch');
Route::delete('/branch/{id}', 'App\Http\Controllers\Company\BranchController@deleteBranch');
Route::post('/branch/{id}/restore', 'App\Http\Controllers\Company\BranchController@restoreBranch');
Route::delete('/branch/{id}/delete', 'App\Http\Controllers\Company\BranchController@permanentDeleteBranch');

Route::get('/xerodata', 'App\Http\Controllers\Xero\XeroController@getXeroData');

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);


Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/create-user', [AuthenticationController::class, 'createUser'])->middleware('auth:sanctum,', 'role'); // middleware that are to be used are separated by a comma                                // \/ This is the middleware that is to be used
// Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware(RoleAuthentication::class);
Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware('auth:sanctum');
