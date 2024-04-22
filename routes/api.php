<?php

use App\Http\Controllers\Authentication\OAuthController;
use App\Http\Controllers\Backend\SuperAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Settings\{AuthenticationSettingsController, ProfileSettingsController};
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\Inventory\{InventoryController, SkuController, WarehouseController};
use App\Http\Controllers\Asset\{AssetController, InsuranceController, MaintenanceController};
use App\Http\Controllers\Schedule\{DeliveryController, PickupController, RouteController ,DeliveryScheduleController};
use App\Http\Controllers\Ticket\{TicketController, WastageController};
use App\Http\Controllers\Report\{ReportController};
use App\Http\Controllers\Driver\DriverController;

// User routes
Route::get('/drivers', 'App\Http\Controllers\Utility\UserController@RetrieveDriver');
Route::get('/managers', 'App\Http\Controllers\Utility\UserController@RetrieveManager');
Route::get('/users', 'App\Http\Controllers\Utility\UserController@RetrieveUsers');
Route::get('/admins', 'App\Http\Controllers\Utility\UserController@RetrieveAdmin');
Route::get('/user', 'App\Http\Controllers\Utility\UserController@RetrieveSingleUser')->middleware('auth:sanctum');

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
Route::get('/xero/connect', 'App\Http\Controllers\Xero\XeroController@xeroConnect');
Route::get('/xero/callback', 'App\Http\Controllers\Xero\XeroController@xeroCallback');
Route::get('/xero/tenant', 'App\Http\Controllers\Xero\XeroController@xeroTenant');
Route::get('/xero/refresh', 'App\Http\Controllers\Xero\XeroController@xeroRefresh');

// Inventory routes
Route::get('/inventory', [InventoryController::class, 'inventory']);
Route::post('/inventory', [InventoryController::class, 'createInventory']);
Route::patch('/inventory/{id}', [InventoryController::class, 'updateInventory']);
Route::delete('/inventory/{id}', [InventoryController::class, 'deleteInventory']);
Route::post('/inventory/restore/{id}', [InventoryController::class, 'restoreInventory']);
Route::delete('/inventory/delete/{id}', [InventoryController::class, 'permanentDeleteInventory']);

// Warehouse routes
Route::get('/warehouse', [WarehouseController::class, 'warehouse']);
Route::post('/warehouse', [WarehouseController::class, 'createWarehouse']);
Route::patch('/warehouse/{id}', [WarehouseController::class, 'updateWarehouse']);
Route::delete('/warehouse/{id}', [WarehouseController::class, 'deleteWarehouse']);
Route::post('/warehouse/restore/{id}', [WarehouseController::class, 'restoreWarehouse']);
Route::delete('/warehouse/delete/{id}', [WarehouseController::class, 'permanentDeleteWarehouse']);


// SKU routes
Route::get('/sku', [SkuController::class, 'sku']);
Route::post('/sku', [SkuController::class, 'createSku']);
Route::patch('/sku/{id}', [SkuController::class, 'updateSku']);

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/backend-login', [AuthenticationController::class, 'backendLogin']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/verify-otp', [AuthenticationController::class, 'verifyOtp']);


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

//Delivery Plan Routes
Route::get('/delivery', [DeliveryScheduleController::class, 'index']);
Route::get('/delivery/{id}', [DeliveryScheduleController::class, 'show']);
Route::post('/delivery', [DeliveryScheduleController::class, 'store']);
Route::patch('/delivery/{id}', [DeliveryScheduleController::class, 'update']);
Route::delete('/delivery/{id}', [DeliveryScheduleController::class, 'destroy']);
Route::post('/delivery/restore/{id}', [DeliveryScheduleController::class, 'restore']);
Route::delete('/delivery/delete/{id}', [DeliveryScheduleController::class, 'permanentDelete']);

// Delivery Schedule routes
Route::post('/schedule/delivery', [DeliveryController::class, 'createDelivery']);
Route::patch('/schedule/delivery/{id}', [DeliveryController::class, 'updateDelivery']);

// 2fa test routes
Route::post('/2fa/generate', [AuthenticationController::class, 'twoFactorGenerate']);
Route::post('/2fa/verify', [AuthenticationController::class, 'verify2FACode']);
Route::post('/2fa/disable', [AuthenticationController::class, 'disable2FA']);


// OAuth for Google
Route::post('/oauth/google', [OAuthController::class, 'OAuthReceive']);

//Ticket Module Routes
Route::get('/ticket', [TicketController::class, 'index']);
Route::get('/ticket/{id}', [TicketController::class, 'show']);
Route::post('/ticket', [TicketController::class, 'store']);
Route::put('/ticket/{ticketNumber}', [TicketController::class, 'update']);
Route::delete('/ticket/{ticketNumber}', [TicketController::class, 'delete']);
Route::post('/ticket/restore/{ticketNumber}', [TicketController::class, 'restore']);
Route::get('/ticket/delete/{ticketNumber}', [TicketController::class, 'permanentDelete']);

//Wastage Mode Routes
Route::get('/waste', [WastageController::class, 'index']);
Route::get('/waste/{id}', [WastageController::class, 'show']);
Route::post('/waste', [WastageController::class, 'store']);
Route::patch('/waste/{id}', [WastageController::class, 'update']);
Route::delete('/waste/{id}', [WastageController::class, 'delete']);
Route::post('/waste/restore/{id}', [WastageController::class, 'restore']);
Route::delete('/waste/delete/{id}', [WastageController::class, 'permanentDelete']);

//  Counting the total number of deliveries, pickups, tickets, users, and assets and single out the specific ones
Route::get('/deliveries/totalorspecific', [ReportController::class, 'getTotalDeliveries']);
Route::get('/pickups/totalorspecific', [ReportController::class, 'getTotalPickups']);
Route::get('/tickets/totalorspecific', [ReportController::class, 'getTotalTickets']);
Route::get('/users/totalorspecific', [ReportController::class, 'getTotalUsers']);
Route::get('/assets/totalorspecific', [ReportController::class, 'getTotalAssets']);

// Logged in user details
Route::get('/fetch-data', [ReportController::class, 'fetchData'])->middleware('auth:sanctum');

Route::middleware('auth:api')->get('/pickup-schedules', [DriverController::class, 'index']);
