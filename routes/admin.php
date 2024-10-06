<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CookApprovalController;
use App\Http\Controllers\Admin\DeliveryAdminController;
use App\Http\Controllers\Admin\GeofencingAreaController;
use App\Http\Controllers\Admin\markupController;
use App\Http\Controllers\Admin\mealapprovalController;
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
    Route::get('users', [UsersController::class, 'index']);
    Route::post('users', [UsersController::class, 'store']);
    Route::get('users/{id}', [UsersController::class, 'edit']);
    Route::put('users/{id}', [UsersController::class, 'update']);
    Route::delete('users/{id}', [UsersController::class, 'destroy']);
    Route::get('roles', [RolesController::class, 'index']);
    Route::post('roles', [RolesController::class, 'store']);
    Route::get('roles/{id}', [RolesController::class, 'edit']);
    Route::put('roles/{id}', [RolesController::class, 'update']);
    Route::delete('roles/{id}', [RolesController::class, 'destroy']);
    Route::get('assignable-roles', [UserRoleController::class, 'index']);
    Route::get('clients', [ClientController::class, 'index']);
    Route::get('cooks', [CookController::class, 'index']);
    Route::get('cooks/active-shifts', [ShiftController::class, 'allShifts']);
    Route::get('admin/shift-time', [ShiftAdminController::class, 'index']);
    Route::post('admin/shift-time', [ShiftAdminController::class, 'store']);
    Route::put('admin/shift-time/{id}', [ShiftAdminController::class, 'update']);

    Route::get('cook/meals', [MealController::class, 'index']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('delivery-companies', [DeliveryAdminController::class, 'index']);
    Route::post('delivery-companies', [DeliveryAdminController::class, 'store']);
    Route::get('markups', [markupController::class, 'index']);
    Route::post('markups', [markupController::class, 'store']);
    Route::get('edit-approve-meal/{id}', [mealapprovalController::class, 'edit']);
    Route::put('update-approve-meal/{id}', [mealapprovalController::class, 'update']);
    Route::get('edit-approve-cook/{id}', [CookApprovalController::class, 'edit']);
    Route::put('approve-cook/{id}', [CookApprovalController::class, 'updateApprovalStatus']);
    Route::post('geofencing', [GeofencingAreaController::class, 'store']);
});
