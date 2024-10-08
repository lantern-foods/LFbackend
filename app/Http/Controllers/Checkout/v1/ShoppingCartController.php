<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customeraddress;
use App\Models\Meal;
use App\Models\Package;
use App\Traits\Numbers;
use App\Traits\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    use Numbers, Orders;

    /**
     * View cart (Booked Orders)
     */
    public function index()
    {
        return $this->getCartItems(false);
    }

    /**
     * View express cart
     */
    public function index_express()
    {
        return $this->getCartItems(true);
    }

    /**
     * Add an item to the cart
     */
    public function store(Request $request)
    {
        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');
        $package_id = $request->input('package_id');
        $qty = $this->clean_monetary_value($request->input('qty'));
        $unit_price = $this->clean_monetary_value($request->input('unit_price'));
        $shift_id = $request->input('shift_id');

        // Validate request
        $validation = $this->validateCartRequest($meal_id, $package_id, $qty, $unit_price);
        if ($validation !== true) {
            return response()->json($validation, 400);
        }

        // Check if meal or package already exists in the cart
        if ($this->itemExistsInCart($client_id, $meal_id, $package_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item already in cart!',
            ], 400);
        }

        // Calculate subtotal and insert item into the cart
        $subtotal = $qty * $unit_price;
        $query = DB::table('cart')->insert([
            'client_id' => $client_id,
            'meal_id' => $meal_id,
            'package_id' => $package_id,
            'shift_id' => $shift_id,
            'qty' => $qty,
            'unit_price' => $unit_price,
            'subtotal' => $subtotal,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $query ? response()->json(['status' => 'success', 'message' => 'Item added to cart!']) :
            response()->json(['status' => 'error', 'message' => 'Failed to add item to cart. Please try again!'], 500);
    }

    /**
     * Edit a meal in the cart
     */
    public function edit($id)
    {
        $meal = DB::table('cart')
            ->join('meals', 'cart.meal_id', '=', 'meals.id')
            ->select('meal_id', 'meal_name', 'qty', 'unit_price')
            ->where('cart.id', $id)
            ->first();

        return $meal ? response()->json(['status' => 'success', 'message' => 'Request successful!', 'data' => $meal]) :
            response()->json(['status' => 'no_data', 'message' => 'Unable to retrieve meal for editing, please try again!'], 404);
    }

    /**
     * Update cart item
     */
    public function update(Request $request, $id)
    {
        $qty = $this->clean_monetary_value($request->input('qty'));
        $unit_price = $this->clean_monetary_value($request->input('unit_price'));

        // Validate quantity and price
        if (!is_numeric($qty) || !is_numeric($unit_price)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid quantity or price!',
            ], 400);
        }

        $subtotal = $qty * $unit_price;
        $query = DB::table('cart')
            ->where('cart.id', $id)
            ->update([
                'qty' => $qty,
                'unit_price' => $unit_price,
                'subtotal' => $subtotal,
                'updated_at' => Carbon::now(),
            ]);

        return $query ? response()->json(['status' => 'success', 'message' => 'Item updated successfully!']) :
            response()->json(['status' => 'error', 'message' => 'Failed to update item. Please try again!'], 500);
    }

    /**
     * Remove an item from the cart
     */
    public function removeFromCart(Request $request)
    {
        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');
        $package_id = $request->input('package_id');

        if (!$meal_id && !$package_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid request!',
            ], 400);
        }

        $query = $meal_id ?
            DB::table('cart')->where('client_id', $client_id)->where('meal_id', $meal_id)->delete() :
            DB::table('cart')->where('client_id', $client_id)->where('package_id', $package_id)->delete();

        return $query ? response()->json(['status' => 'success', 'message' => 'Item removed from cart successfully!']) :
            response()->json(['status' => 'error', 'message' => 'Failed to remove item. Please try again!'], 500);
    }

    /**
     * Delete cart item
     */
    public function delete($id)
    {
        $query = DB::table('cart')->where('id', $id)->delete();

        return $query ? response()->json(['status' => 'success', 'message' => 'Cart item deleted successfully!']) :
            response()->json(['status' => 'error', 'message' => 'Failed to delete cart item. Please try again!'], 500);
    }

    /**
     * Submit final cart data for booked orders
     */
    public function submitFinalCartData(Request $request)
    {
        return $this->submitCartData($request, false);
    }

    /**
     * Submit final cart data for express orders
     */
    public function submitFinalCartDataExpress(Request $request)
    {
        return $this->submitCartData($request, true);
    }

    /**
     * Helper: Submit final cart data (booked/express)
     */
    private function submitCartData(Request $request, bool $isExpress)
    {
        $client_id = Auth::id();
        $meals_to_update = $request->input('meals', []);
        $packages_to_update = $request->input('packages', []);

        if (empty($meals_to_update) && empty($packages_to_update)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select items to checkout',
            ], 400);
        }

        // Update meals and packages in the cart
        foreach ($meals_to_update as $meal) {
            DB::table('cart')
                ->where('client_id', $client_id)
                ->where('id', $meal['cart_meal_item_id'])
                ->update([
                    'selected' => 1,
                    'qty' => $meal['final_quantity'],
                    'subtotal' => $this->updateCartItemSubtotal($meal['cart_meal_item_id'], $meal['final_quantity']),
                ]);

            if ($isExpress) {
                $this->mealActionOnCheckout($meal['cart_meal_item_id']);
            }
        }

        foreach ($packages_to_update as $package) {
            DB::table('cart')
                ->where('client_id', $client_id)
                ->where('id', $package['cart_package_item_id'])
                ->update([
                    'selected' => 1,
                    'qty' => $package['final_quantity'],
                    'subtotal' => $this->updateCartItemSubtotal($package['cart_package_item_id'], $package['final_quantity']),
                ]);

            if ($isExpress) {
                $this->packageActionOnCheckout($package['cart_package_item_id']);
            }
        }

        // Create the order based on the type (booked/express)
        return $isExpress ? $this->createExpressOrderAction($request) : $this->createBookedOrderAction($request);
    }

    /**
     * Helper: Calculate subtotal for a cart item
     */
    private function updateCartItemSubtotal($id, $qty)
    {
        $cart_item = Cart::find($id);
        return $qty * $cart_item->unit_price;
    }

    /**
     * Helper: Get cart items (booked or express)
     */
    private function getCartItems(bool $isExpress)
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal.meal_images'])
            ->where('client_id', $client_id)
            ->whereNull('package_id')
            ->when($isExpress, function ($query) {
                $query->whereNotNull('shift_id');
            }, function ($query) {
                $query->whereNull('shift_id');
            })
            ->where('selected', 0)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal.meal_images'])
            ->where('client_id', $client_id)
            ->whereNotNull('package_id')
            ->when($isExpress, function ($query) {
                $query->whereNotNull('shift_id');
            }, function ($query) {
                $query->whereNull('shift_id');
            })
            ->where('selected', 0)
            ->get();

        $active_address = Customeraddress::where('location_status', 1)
            ->where('client_id', $client_id)
            ->first();

        if ($meals->isEmpty() && $packages->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'total' => $this->computeCartTotal(),
                'message' => 'Your cart is empty!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'total' => $this->computeCartTotal(),
            'delivery_cost' => 0,
            'address' => $active_address,
            'meals' => $meals,
            'packages' => $packages,
        ]);
    }

    /**
     * Helper: Check if meal or package is already in the cart
     */
    private function itemExistsInCart($client_id, $meal_id, $package_id)
    {
        $mealExists = DB::table('cart')
            ->where('client_id', $client_id)
            ->where('meal_id', $meal_id)
            ->exists();

        $packageExists = DB::table('cart')
            ->where('client_id', $client_id)
            ->where('package_id', $package_id)
            ->exists();

        return $mealExists || $packageExists;
    }

    /**
     * Helper: Validate cart request data
     */
    private function validateCartRequest($meal_id, $package_id, $qty, $unit_price)
    {
        if (!isset($meal_id) && !isset($package_id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid request! Item must be either a meal or a package.',
            ];
        }

        if (isset($meal_id) && isset($package_id)) {
            return [
                'status' => 'error',
                'message' => 'Invalid request! A cart item can either be a meal or a package, not both.',
            ];
        }

        if (!is_numeric($qty) || !is_numeric($unit_price)) {
            return [
                'status' => 'error',
                'message' => 'Invalid quantity or price!',
            ];
        }

        return true;
    }
}
