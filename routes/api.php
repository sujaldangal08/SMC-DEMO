<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleAuthentication;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Settings\{AuthenticationSettingsController, ProfileSettingsController};
use App\Http\Controllers\SalesOrderController;
use PHPUnit\Framework\TestStatus\Success;
use App\Http\Controllers\Inventory\{InventoryController, SkuController, WarehouseController};
use Symfony\Component\HttpKernel\Profiler\Profile;
use App\Http\Controllers\Asset\{AssetController, InsuranceController, MaintenanceController};
use App\Http\Controllers\Schedule\{DeliveryController, PickupController, RouteController};

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// User routes
Route::get('/drivers', 'App\Http\Controllers\Utility\UserController@RetreiveDriver');
Route::get('/managers', 'App\Http\Controllers\Utility\UserController@RetreiveManager');
Route::get('/users', 'App\Http\Controllers\Utility\UserController@RetreiveUser');
Route::get('/admins', 'App\Http\Controllers\Utility\UserController@RetreiveAdmin');

// Company Branch Routes
Route::get('/branch', 'App\Http\Controllers\Company\BranchController@branch');
Route::get('/branch/{id}', 'App\Http\Controllers\Company\BranchController@branchSingle');
Route::post('/branch', 'App\Http\Controllers\Company\BranchController@createBranch');
Route::patch('/branch/{id}', 'App\Http\Controllers\Company\BranchController@updateBranch');
Route::delete('/branch/{id}', 'App\Http\Controllers\Company\BranchController@deleteBranch');
Route::post('/branch/{id}/restore', 'App\Http\Controllers\Company\BranchController@restoreBranch');
Route::delete('/branch/{id}/delete', 'App\Http\Controllers\Company\BranchController@permanentDeleteBranch');

Route::patch('/company/{id}', 'App\Http\Controllers\Company\CompanyController@updateCompany');
Route::get('/company', 'App\Http\Controllers\Company\CompanyController@Company');

// xero routes
Route::get('/xerodata', 'App\Http\Controllers\Xero\XeroController@getXeroData');
Route::get('/purchaseorder', 'App\Http\Controllers\Xero\XeroController@getPurchaseOrder');

Route::get('/sendemail/{email}', 'App\Http\Controllers\EmailController@sendEmail');

// Inventory routes
Route::get('/inventory', [InventoryController::class, 'inventory']);
Route::post('/inventory', [InventoryController::class, 'createInventory']);
Route::patch('/inventory/{id}', [InventoryController::class, 'updateInventory']);
Route::delete('/inventory/{id}', [InventoryController::class, 'deleteInventory']);
Route::post('/inventory/restore/{id}', [InventoryController::class, 'restoreInventory']);
Route::delete('/inventory/delete/{id}', [InventoryController::class, 'permanentDeleteInventory']);

// Warehouse routes
Route::get('/warehouse', [InventoryController::class, 'warehouse']);
Route::post('/warehouse', [InventoryController::class, 'createWarehouse']);
Route::patch('/warehouse/{id}', [InventoryController::class, 'updateWarehouse']);
Route::delete('/warehouse/{id}', [InventoryController::class, 'deleteWarehouse']);
Route::post('/warehouse/restore/{id}', [InventoryController::class, 'restoreWarehouse']);
Route::delete('/warehouse/delete/{id}', [InventoryController::class, 'permanentDeleteWarehouse']);


// SKU routes
Route::get('/sku', [InventoryController::class, 'sku']);
Route::post('/sku', [InventoryController::class, 'createSku']);
Route::patch('/sku/{id}', [InventoryController::class, 'updateSku']);

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

// Schedule Module Routes

// Routes for scheduling
Route::get('/route', [RouteController::class, 'index']);
Route::get('/route/{id}', [RouteController::class, 'show']);
Route::post('/route', [RouteController::class, 'store']);
Route::patch('/route/{id}', [RouteController::class, 'update']);
Route::delete('/route/{id}', [RouteController::class, 'delete']);
Route::post('/route/restore/{id}', [RouteController::class, 'restore']);
Route::delete('/route/delete/{id}', [RouteController::class, 'permanentDelete']);

// Pickup Schedule routes
Route::get('/schedule/pickup', [PickupController::class, 'index']);
Route::get('/schedule/pickup/{id}', [PickupController::class, 'show']);
Route::post('/schedule/pickup', [PickupController::class, 'store']);
Route::patch('/schedule/pickup/{id}', [PickupController::class, 'update']);
Route::delete('/schedule/pickup/{id}', [PickupController::class, 'destroy']);
Route::post('/schedule/pickup/restore/{id}', [PickupController::class, 'restore']);
Route::delete('/schedule/pickup/delete/{id}', [PickupController::class, 'permanentDelete']);

// Delivery Schedule routes
Route::post('/schedule/delivery', [DeliveryController::class, 'createDelivery']);
Route::patch('/schedule/delivery/{id}', [DeliveryController::class, 'updateDelivery']);


