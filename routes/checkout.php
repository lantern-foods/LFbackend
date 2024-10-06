<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Checkout\v1\ShoppingCartController;
use App\Http\Controllers\Checkout\v1\OrdersController;
use App\Http\Controllers\Checkout\v1\PaymentController;
use App\Http\Controllers\Checkout\v1\MpesaSTKPushResponseController;

Route::middleware(['auth:api-clients'])->group(function () {
	Route::group(['prefix' => 'v1'], function () {
		Route::get('view-cart', [ShoppingCartController::class, 'index']);
		Route::get('view-cart/express', [ShoppingCartController::class, 'index_express']);
		Route::post('add-to-cart', [ShoppingCartController::class, 'store']);
		Route::post('remove-from-cart', [ShoppingCartController::class, 'removeFromCart']);
		Route::post('submit-cart', [ShoppingCartController::class, 'submitFinalCartData']);
		Route::post('submit-cart/express', [ShoppingCartController::class, 'submitFinalCartDataExpress']);
		Route::get('edit-cart/{id}', [ShoppingCartController::class, 'edit']);
		Route::put('update-cart/{id}', [ShoppingCartController::class, 'update']);
		Route::delete('delete-from-cart/{id}', [ShoppingCartController::class, 'delete']);
		Route::put('update-cart-address/{id}', [ShoppingCartController::class, 'updateAddressStatus']);
		Route::post('create-order', [OrdersController::class, 'createOrder']);
		Route::post('mpesa/payment', [PaymentController::class, 'initiateMpesaPayment']);
		Route::get('order/{id}', [OrdersController::class, 'get_order']);
	});
});

//Needs to be a public endpoint since it receives notifications from M-Pesa API
Route::post('mpesa/stk-callback', [MpesaSTKPushResponseController::class, 'processResponse']);
