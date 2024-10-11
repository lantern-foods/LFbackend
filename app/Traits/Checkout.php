<?php

namespace App\Traits;

use App\Models\Meal;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Checkout
{
    /**
     * Get the total amount of a given order
     *
     * @param string $order_id
     * @return float
     */
    public function getOrderTotal($order_id)
    {
        $order = DB::table('orders')
            ->where('order_no', $order_id)
            ->select('order_total')
            ->first();

        return !empty($order) ? $order->order_total : 0;
    }

    /**
     * Compute the total price for the given meals array
     *
     * @param array $meals
     * @return float
     */
    protected function computeMealPrice($meals)
    {
        $price = 0;

        foreach ($meals as $meal) {
            $mealId = $meal['meal_id'];
            $quantity = $meal['quantity'];

            $mealInfo = Meal::find($mealId);

            if ($mealInfo) {
                $price += $mealInfo->meal_price * $quantity;
            }
        }

        return $price;
    }

    /**
     * Compute the total for selected items in the cart (meals and packages)
     *
     * @return float
     */
    public function computeSelectedCartTotal()
    {
        $client_id = Auth::id();

        // Fetch selected meals from the cart
        $meals = DB::table('cart')
            ->join('meals', 'cart.meal_id', '=', 'meals.id')
            ->select('cart.id as cart_id', 'cart.meal_id', 'meal_name', 'qty', 'unit_price', 'subtotal')
            ->where('client_id', $client_id)
            ->whereNull('package_id') // Only fetch meals, no packages
            ->where('selected', 1)
            ->get();

        // Fetch selected packages from the cart
        $packages = Cart::with(['package.packageMeals.meal'])
            ->where('client_id', $client_id)
            ->whereNotNull('package_id') // Only fetch packages
            ->where('selected', 1)
            ->get();

        // Calculate total
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
