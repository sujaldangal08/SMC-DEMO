<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleAuthentication;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Settings\AuthenticationSettingsController;
use App\Http\Controllers\Backend\SuperAdminController;
use App\Http\Controllers\SalesOrderController;
use PHPUnit\Framework\TestStatus\Success;
use App\Http\Controllers\Settings\ProfileSettingsController;
use Symfony\Component\HttpKernel\Profiler\Profile;
use App\Http\Controllers\Asset\AssetController;
use App\Http\Controllers\Asset\InsuranceController;
use App\Http\Controllers\Asset\MaintenanceController;
use App\Http\Controllers\InventoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/branch', 'App\Http\Controllers\Company\BranchController@branch');
Route::get('/branch/{id}', 'App\Http\Controllers\Company\BranchController@branchSingle');
Route::post('/branch', 'App\Http\Controllers\Company\BranchController@createBranch');
Route::patch('/branch/{id}', 'App\Http\Controllers\Company\BranchController@updateBranch');
Route::delete('/branch/{id}', 'App\Http\Controllers\Company\BranchController@deleteBranch');
Route::post('/branch/{id}/restore', 'App\Http\Controllers\Company\BranchController@restoreBranch');
Route::delete('/branch/{id}/delete', 'App\Http\Controllers\Company\BranchController@permanentDeleteBranch');

Route::get('/xerodata', 'App\Http\Controllers\Xero\XeroController@getXeroData');
Route::get('/company', 'App\Http\Controllers\Company\CompanyController@Company');
Route::patch('/company/{id}', 'App\Http\Controllers\Company\CompanyController@updateCompany');

Route::get('/purchaseorder', 'App\Http\Controllers\Xero\XeroController@getPurchaseOrder');


Route::get('/sendemail/{email}', 'App\Http\Controllers\EmailController@sendEmail');

Route::get('/inventory', [InventoryController::class, 'inventory']);
Route::get('/sku/{sku}', [InventoryController::class, 'inventoryBySKU']);
Route::get('/warehouse', [InventoryController::class, 'warehouse']);
Route::post('/inventory', [InventoryController::class, 'inventory']);
Route::patch('/inventory/{SKU}', [InventoryController::class, 'updateInventory']);



Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('backend-login', [AuthenticationController::class, 'backendLogin']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::post('/register', [AuthenticationController::class, 'register']);


Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:sanctum');
// Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware(RoleAuthentication::class);
Route::get('/dashboard', [AuthenticationController::class, 'dashboard'])->middleware('auth:sanctum');
Route::patch('/profile', [ProfileSettingsController::class, 'updateProfile'])->middleware('auth:sanctum');


// Super Admin Routes
Route::get('/setting/auth-attempts', [AuthenticationSettingsController::class, 'authAttempts'])->middleware('auth:sanctum', 'role:super-admin');
Route::get('/setting/auth-attempts/{id}', [AuthenticationSettingsController::class, 'getOneAttempt'])->middleware('auth:sanctum', 'role:super-admin');
Route::patch('/setting/auth-attempts/{id}', [AuthenticationSettingsController::class, 'updateAttempts'])->middleware('auth:sanctum', 'role:super-admin');

Route::post('/super-admin', [SuperAdminController::class, 'createSuperAdmin'])->middleware('auth:sanctum', 'role:super-admin');
Route::get('/super-admins', [SuperAdminController::class, 'getAll'])->middleware('auth:sanctum', 'role:super-admin');

Route::delete('/super-admin/{id}', [SuperAdminController::class, 'destroy']);
Route::post('/create-user', [AuthenticationController::class, 'createUser'])->middleware('auth:sanctum,', 'role:super-admin');
Route::delete('/admins/{id}', [ProfileSettingsController::class, 'getAllSAdmin'])->middleware('auth:sanctum', 'role:super-admin');


//Asset Module Routes

// Asset routes
Route::get('/asset', [AssetController::class, 'getAll']);
Route::get('/asset/{id}', [AssetController::class, 'getOne']);
Route::post('/asset', [AssetController::class, 'createAsset']);
Route::patch('/asset/{id}', [AssetController::class, 'updateAsset']);
Route::delete('/asset/{id}', [AssetController::class, 'deleteAsset']);
Route::post('/asset/restore/{id}', [AssetController::class, 'restoreAsset']);
Route::delete('/asset/delete/{id}', [AssetController::class, 'permanentDeleteAsset']);

// Insurances routes
Route::get('/insurance', [InsuranceController::class, 'getAllInsurance']);
Route::get('/insurance/{id}', [InsuranceController::class, 'getOneInsurance']);
Route::post('/insurance', [InsuranceController::class, 'createInsurance']);
Route::patch('/insurance/{id}', [InsuranceController::class, 'updateInsurance']);
Route::delete('/insurance/{id}', [InsuranceController::class, 'deleteInsurance']);
Route::post('/insurance/restore/{id}', [InsuranceController::class, 'restoreInsurance']);
Route::delete('/insurance/delete/{id}', [InsuranceController::class, 'permanentDeleteInsurance']);

// Maintenance routes
Route::get('/maintenance', [MaintenanceController::class, 'getAllMaintenance']);
Route::get('/maintenance/{id}', [MaintenanceController::class, 'getOneMaintenance']);
Route::post('/maintenance', [MaintenanceController::class, 'createMaintenance']);
Route::patch('/maintenance/{id}', [MaintenanceController::class, 'updateMaintenance']);
Route::delete('/maintenance/{id}', [MaintenanceController::class, 'deleteMaintenance']);
Route::post('/maintenance/restore/{id}', [MaintenanceController::class, 'restoreMaintenance']);
Route::delete('/maintenance/delete/{id}', [MaintenanceController::class, 'permanentDeleteMaintenance']);

//Sales Order Routes
Route::get('/sales-orders', [SalesOrderController::class, 'store']);
Route::get('/sales-orders/{id}', [SalesOrderController::class, 'show']);
