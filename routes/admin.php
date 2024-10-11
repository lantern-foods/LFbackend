<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CookApprovalController;
use App\Http\Controllers\Admin\DeliveryAdminController;
use App\Http\Controllers\Admin\GeofencingAreaController;
use App\Http\Controllers\Admin\MarkupController;
use App\Http\Controllers\Admin\MealApprovalController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\ShiftAdminController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Client\v1\ClientController;
use App\Http\Controllers\Cook\v1\CookController;
use App\Http\Controllers\Cook\v1\ShiftController;
use App\Http\Controllers\Meal\v1\MealController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'authenticate']);

Route::middleware(['auth:api'])->group(function () {

    // Admin-related routes grouped under a prefix
    Route::prefix('admin')->name('admin.')->group(function () {

        // User routes
        Route::resource('users', UsersController::class)->except(['create', 'show']); // Use resource for simplicity

        // Roles routes
        Route::resource('roles', RolesController::class)->except(['create', 'show']);

        // Assignable Roles
        Route::get('assignable-roles', [UserRoleController::class, 'index'])->name('assignable-roles');

        // Shift Admin
        Route::get('shift-time', [ShiftAdminController::class, 'index'])->name('shift-time.index');
        Route::post('shift-time', [ShiftAdminController::class, 'store'])->name('shift-time.store');
        Route::put('shift-time/{id}', [ShiftAdminController::class, 'update'])->name('shift-time.update');

        // Cook Approval
        Route::get('edit-approve-cook/{id}', [CookApprovalController::class, 'edit'])->name('cook-approval.edit');
        Route::put('approve-cook/{id}', [CookApprovalController::class, 'updateApprovalStatus'])->name('cook-approval.update');

        // Meal Approval
        Route::get('edit-approve-meal/{id}', [MealApprovalController::class, 'edit'])->name('meal-approval.edit');
        Route::put('update-approve-meal/{id}', [MealApprovalController::class, 'update'])->name('meal-approval.update');

        // Delivery Companies
        Route::get('delivery-companies', [DeliveryAdminController::class, 'index'])->name('delivery-companies.index');
        Route::post('delivery-companies', [DeliveryAdminController::class, 'store'])->name('delivery-companies.store');

        // Markups
        Route::get('markups', [MarkupController::class, 'index'])->name('markups.index');
        Route::post('markups', [MarkupController::class, 'store'])->name('markups.store');

        // Geofencing
        Route::post('geofencing', [GeofencingAreaController::class, 'store'])->name('geofencing.store');
    });

    // Cook and Client-related routes
    Route::get('cooks', [CookController::class, 'index']);
    Route::get('cooks/active-shifts', [ShiftController::class, 'allShifts']);
    Route::get('clients', [ClientController::class, 'index']);

    // Meals
    Route::get('cook/meals', [MealController::class, 'index']);

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
});
