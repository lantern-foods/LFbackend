<?php

   use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\Driver\v1\AuthController;
   use App\Http\Controllers\Driver\v1\driververificationController;
   use App\Http\Controllers\Driver\v1\orderpickupController;
   use App\Http\Controllers\Driver\v1\clientratingController;
   use App\Http\Controllers\Driver\v1\cookratingController;
   use App\Http\Controllers\Deliverycompany\v1\DriverController;

   

   Route::group(['prefix' => 'v1'], function () {
        //delivery company opt verification
        Route::post('drivers-otp-verify', [driververificationController::class, 'verifyDrivOtp']);

        //delivery company set password after verification success

        Route::post('set-rider-password', [driververificationController::class, 'setdriverPassword']);


        Route::post('rider-company-login', [AuthController::class, 'authenticate']);

   });

   Route::middleware(['auth:api-driver'])->group(function () {
        Route::group(['prefix' => 'v1'], function () {
            
          Route::get('driver/order-ready', [orderpickupController::class, 'getReadyForPickupOrders']);
          Route::post('driver/order-accept/{id}', [orderpickupController::class, 'startOrderPickup']);
          Route::post('driver/cook-pick-order/{id}', [orderpickupController::class, 'verifyOtpAndUpdateOrder']);
          Route::get('driver/active-orders', [orderpickupController::class, 'client_delivery']);
          Route::get('driver/active-order/{id}', [orderpickupController::class, 'get_client_delivery']);
          Route::post('driver/client-delivery-order/{id}', [orderpickupController::class, 'clientOtpAndDeliverOrder']);
          Route::get('driver/delivered-orders', [orderpickupController::class,'deliveredOrders']);
          Route::get('driver/delivered-order/{id}', [orderpickupController::class,'deliveredOrder']);
          Route::get('driver/driver-edit/{id}', [DriverController::class, 'edit']);
          Route::put('driver/driver-update/{id}', [DriverController::class, 'update']);
          Route::post('driver/cook-rating', [cookratingController::class, 'rateCook']);
          Route::post('driver/client-rating', [clientratingController::class, 'rateClient']);
          Route::get('driver/analytics', [orderpickupController::class, 'driverAnalytics'])->name('driver.analytics');

        });
   });
