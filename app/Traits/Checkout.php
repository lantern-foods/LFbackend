<?php

namespace App\Traits;

use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Checkout
{

	/**
	 * Get order total
	 */
	public function getOrderTotal($order_id)
	{
		$order = DB::table('orders')->where('order_no', $order_id)->select('order_total')->first();

		if (!empty($order)) {
			$order_total = $order->order_total;
		} else {
			$order_total = 0;
		}

		return $order_total;
	}

	protected function computeMealPrice($meals)
	{
		$price = 0;
		foreach ($meals as $meal) {

			$mealId = $meal['meal_id'];
			$quantity = $meal['quantity'];
			$mealInfo = Meal::find($mealId);
			if ($meal) {
				$price += $mealInfo->meal_price * $quantity;
			}
		}
		return $price;
	}


	public function computeSelectedCartTotal()
	{
		$client_id = Auth::id();


		$meals = DB::table('cart')
			->join('meals', 'cart.meal_id', '=', 'meals.id')
			->select('cart.id as cart_id', 'cart.meal_id', 'meal_name', 'qty', 'unit_price', 'subtotal')
			->where('client_id', $client_id)
			->where('package_id', null)
			->where('selected', 1)
			->get();

		$packages = Cart::with(['package.packageMeals.meal'])
			->where('client_id', $client_id)
			->where('package_id', '!=', null)
			->where('selected', 1)
			->get();

		$total = 0;

		foreach ($meals as $meal) {
			$total += $meal->subtotal;
		}
		foreach ($packages as $package) {
			$total += $package->subtotal;
		}

		return $total;
	}
}
