<?php

use App\Http\Controllers\Checkout\v1\OrdersController;
use App\Http\Controllers\Checkout\v1\PaymentController;
use App\Http\Controllers\Client\v1\AuthController;
use App\Http\Controllers\Client\v1\ClientaddressController;
use App\Http\Controllers\Client\v1\ClientController;
use App\Http\Controllers\Client\v1\ClientFavoriteController;
use App\Http\Controllers\Client\v1\ClientverificationController;
use App\Http\Controllers\Client\v1\FavoriteCooksController;
use App\Http\Controllers\Client\v1\FavoriteMealsController;
use App\Http\Controllers\Client\v1\OrderRatingController;
use App\Http\Controllers\Cook\v1\CookController;
use App\Http\Controllers\Cook\v1\ShiftController;
use App\Http\Controllers\Meal\v1\MealController;
use App\Http\Controllers\MealPackageRatingController;
use App\Models\MealPackageRating;
use Illuminate\Support\Facades\Route;

Route::get('test',function (\Illuminate\Http\Request $request){
    $message = $request->query('name', 'Helen');
   return response()->json(['message' => $message]);
});
Route::group(['prefix' => 'v1'], function () {
    //Client registration
    Route::post('client-register', [ClientController::class, 'store']);

    //Client OTP verification
    Route::post('client-otp-verify', [ClientverificationController::class, 'verifyOtp']);

    //Client set password after verification success
    Route::post('set-password', [ClientverificationController::class, 'setPassword']);

    //Client Login
    Route::post('client-login', [AuthController::class, 'authenticate']);
});

Route::middleware(['auth:api-clients'])->group(function () {
    Route::group(['prefix' => 'v1'], function () {
        Route::get('client/get-profile/{id}', [ClientController::class, 'edit']);
        Route::put('client/update-profile/{id}', [ClientController::class, 'update']);
        Route::post('client-logout', [AuthController::class, 'logout']);
        Route::get('cook-meals', [MealController::class, 'index']);

        Route::get('client-meals/express', [MealController::class, 'all_cook_meals_express']);
        Route::get('cook-meal/{id}', [MealController::class, 'get_meals']);
        Route::get('client/orders', [OrdersController::class, 'get_orders']);
        Route::get('client/orders/{id}', [OrdersController::class, 'client_order']);
        Route::get('cooks-meals', [CookController::class, 'all_cook_meals']);
        Route::get('cooks/active-shifts-meals', [ShiftController::class, 'allShiftsmeals']);
        Route::get('cooks-meal/{id}', [CookController::class, 'all_cook_meal']);
        Route::get('client/customer-address', [ClientaddressController::class, 'all_address']);
        Route::post('client/customer-addresses', [ClientaddressController::class, 'add_address']);
        Route::get('client/customer-address/{id}', [ClientaddressController::class, 'edit']);
        Route::put('client/customer-address/{id}', [ClientaddressController::class, 'update']);
        Route::delete('delete-address/{id}', [ClientaddressController::class, 'deleteAddress']);
        Route::get('clients/payments', [PaymentController::class, 'all_payments']);
        Route::post('rate-order/{orderId}', [OrderRatingController::class, 'rateOrder'])->name('rate.order');
        Route::get('client-ratings', [OrderRatingController::class, 'showClientRatings'])->name('client.ratings');
        Route::post('client/favorite-meals', [FavoriteMealsController::class, 'store']);
        Route::get('client/favorite-meals', [FavoriteMealsController::class, 'index']);
        Route::delete('client/favorite-meals/{id}', [FavoriteMealsController::class, 'destroy']);
        Route::post('client/favorite-cook', [FavoriteCooksController::class, 'store']);
        Route::get('client/favorite-cooks', [FavoriteCooksController::class, 'index']);
        Route::delete('client/favorite-cook/{id}', [FavoriteCooksController::class, 'destroy']);

        Route::get('client/get-favorite-meals', [ClientFavoriteController::class, 'get_favourite_meals']);
        Route::delete('client/delete-favorite-meal/{id}', [ClientFavoriteController::class, 'delete_favourite_meal']);
        Route::post('client/add-favorite-meal', [ClientFavoriteController::class, 'add_favourite_meal']);
        Route::get('client/get-favorite-cooks', [ClientFavoriteController::class, 'get_favourite_cooks']);
        Route::post('client/add-favorite-cook', [ClientFavoriteController::class, 'add_favourite_cook']);
        Route::delete('client/delete-favorite-cook/{id}', [ClientFavoriteController::class, 'delete_favourite_cook']);
        // cart client
        Route::post('client/add-to-cart', [OrdersController::class, 'addToCart']);
        Route::post('client/rate-item', [MealPackageRatingController::class, 'store']);
        Route::get('client/meal-ratings/{meal_id}', [MealPackageRatingController::class, 'showMealRatings']);
        Route::get('client/package-ratings/{package_id}', [MealPackageRatingController::class, 'showPackageRatings']);

    });
});
