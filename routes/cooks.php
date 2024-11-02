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
        // Existing CookController routes
        Route::post('cook/register', [CookController::class, 'store']);
        Route::post('cook/upload-documents', [CookController::class, 'uploadDocuments']);
        Route::get('cook/profile/{id}', [CookController::class, 'profile']);
        Route::put('cook/update-profile/{id}', [CookController::class, 'update']);

        // Meals routes for Cook
        Route::get('cook/cook_meals/{id}', [MealController::class, 'cook_meals']);
        Route::post('cook/create-meal', [MealController::class, 'store']);
        Route::post('cook/upload-meal-image', [MealController::class, 'uploadImages']);
        Route::get('cook/meal-partial-edit/{id}', [MealController::class, 'edit']);
        Route::put('cook/meal-partial-update/{id}', [MealController::class, 'update']);
        Route::get('cook/meal-full-edit/{id}', [MealController::class, 'mealFullEdit']);
        Route::put('cook/meal-full-update/{id}', [MealController::class, 'mealFullUpdate']);

        // cook meals and express meals routes
        Route::get('cook/cook-meals', [CookController::class, 'all_cook_meals']);
        Route::get('cook/cook-meals/express', [CookController::class, 'all_cook_meals_express']);

        // ShiftController routes
        Route::post('cook/create-shift', [ShiftController::class, 'store']);
        Route::get('cook/shift/{id}', [ShiftController::class, 'getShift']);
        Route::put('cook/shift-update/{id}', [ShiftController::class, 'update']);
        Route::put('cook/shift-end/{id}', [ShiftController::class, 'endShiftAction']);
        Route::get('cook/all-shifts', [ShiftController::class, 'allShifts']);

        // PackageController routes
        Route::get('cook/packages', [PackageController::class, 'index']);
        Route::post('cook/create-packages', [PackageController::class, 'create']);
        Route::post('cook/edit-packages', [PackageController::class, 'editPackage']);

        // Orders and Transactions
        Route::get('cook/new-orders/{id}', [CookOrdersController::class, 'pending_orders']);
        Route::get('cook/single-order/{id}', [CookOrdersController::class, 'orders_ready']);
        Route::put('cook/update-new-orders/{id}', [CookOrdersController::class, 'order_ready']);
        Route::get('cook/all-transactions/{id}', [CookController::class, 'all_transactions']);
    });
});
