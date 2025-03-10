<?php

use App\Http\Controllers\Admin\DeliveryAdminController;
use App\Http\Controllers\Deliverycompany\v1\AuthController;
use App\Http\Controllers\Deliverycompany\v1\deliverycompanyverificationController;
use App\Http\Controllers\Deliverycompany\v1\DriverController;
use App\Http\Controllers\Deliverycompany\v1\orderassignmentController;
use App\Http\Controllers\Deliverycompany\v1\VehicleController;
use App\Http\Controllers\Deliverycompany\v1\VehicleAllocationController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // delivery company opt verification
    Route::post('delivery-company-otp-verify', [deliverycompanyverificationController::class, 'verifyDelOtp']);

    // delivery company set password after verification success

    Route::post('set-delivery-password', [deliverycompanyverificationController::class, 'setdeliverycompanyPassword']);

    Route::post('delivery-company-login', [AuthController::class, 'authenticate']);
});

Route::middleware(['auth:api-delivery-company'])->group(function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::get('delivery-company/company-edit/{id}', [DeliveryAdminController::class, 'edit']);
        Route::put('delivery-company/company-update-edit/{id}', [DeliveryAdminController::class, 'update']);
        Route::get('delivery-company/drivers', [DriverController::class, 'index']);
        Route::post('delivery-company/create-driver', [DriverController::class, 'store']);
        Route::post('delivery-company/upload-driver-image', [DriverController::class, 'UploadImages']);
        Route::post('delivery-company/driver-otp', [DriverController::class, 'sendDriverOtp']);
        Route::get('delivery-company/driver-edit/{id}', [DriverController::class, 'edit']);
        Route::put('delivery-company/driver-update/{id}', [DriverController::class, 'update']);
        Route::post('delivery-company/driver-allocate', [VehicleAllocationController::class, 'allocate']);

        Route::get('delivery-company/vehicles', [VehicleController::class, 'index']);
        Route::post('delivery-company/create-vehicles', [VehicleController::class, 'store']);
        Route::get('delivery-company/vehicle-edit/{id}', [VehicleController::class, 'edit']);
        Route::put('delivery-company/vehicle-update/{id}', [VehicleController::class, 'update']);

        Route::post('delivery-company/driver-order-assign', [orderassignmentController::class, 'assignOrdersToRider']);
    });
});
