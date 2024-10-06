<?php

use App\Http\Controllers\Admin\ShiftAdminController;
use App\Http\Controllers\Cook\v1\CookController;
use App\Http\Controllers\Cook\v1\CookOrdersController;
use App\Http\Controllers\Cook\v1\PackageController;
use App\Http\Controllers\Cook\v1\ShiftController;
use App\Http\Controllers\Meal\v1\MealController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api-clients'])->group(function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::post('cook/register', [CookController::class, 'store']);
        Route::post('cook/upload-documents', [CookController::class, 'UploadDocuments']);
        Route::get('cook/profile/{id}', [CookController::class, 'edit']);
        Route::get('cook/profile/{id}', [CookController::class, 'profile']);
        Route::put('cook/update-profile/{id}', [CookController::class, 'update']);
        Route::get('cook/cook_meals/{id}', [MealController::class, 'cook_meals']);
        Route::post('cook/create-meal', [MealController::class, 'store']);
        Route::post('cook/upload-meal-image', [MealController::class, 'uploadImages']);
        Route::get('cook/meal-partial-edit/{id}', [MealController::class, 'edit']);
        Route::put('cook/meal-partial-update/{id}', [MealController::class, 'update']);
        Route::get('cook/meal-full-edit/{id}', [MealController::class, 'mealFullEdit']);
        Route::put('cook/meal-full-update/{id}', [MealController::class, 'mealFullUpdate']);
        Route::post('cook/create-shift', [ShiftController::class, 'store']);
        Route::get('cook/shift/{id}', [ShiftController::class, 'getShift']);
        // shift
        Route::post('cook/shift-edit/{id}/meals', [ShiftController::class, 'updateShiftMeals']);
        Route::put('cook/shift-update/{id}', [ShiftController::class, 'update']);  // n
        Route::put('cook/shift-end/{id}', [ShiftController::class, 'updateShiftstatus']);
        Route::get('cook/shift-meals/{id}', [ShiftController::class, 'getShiftMeals']);
        Route::get('cook/all-shifts', [ShiftController::class, 'allShifts']);
        Route::post('cook/shift/{id}/meal-exist', [ShiftController::class, 'checkIfMealExist']);
        // end shift
        Route::get('total-earnings-shift', [ShiftController::class, 'getTotalEarningsPerCookPerShiftDate']);
        Route::get('cook/packages', [PackageController::class, 'index']);
        // Route::post('cook/create-packages', [PackageController::class, 'store']);
        Route::post('cook/create-packages', [PackageController::class, 'create']);
        Route::post('cook/edit-packages', [PackageController::class, 'editPackage']);
        Route::get('cook/show-packages/{id}', [PackageController::class, 'show']);
        Route::get('cook/edit-packages/{id}', [PackageController::class, 'edit']);
        Route::post('cook/update-packages/{id}', [PackageController::class, 'update']);
        Route::get('cook/new-orders/{id}', [CookOrdersController::class, 'pending_orders']);
        Route::get('cook/single-order/{id}', [CookOrdersController::class, 'orders_ready']);
        Route::put('cook/update-new-orders/{id}', [CookOrdersController::class, 'order_ready']);
        Route::get('cook/all-transactions/{id}', [CookController::class, 'all_transactions']);
    });
});
