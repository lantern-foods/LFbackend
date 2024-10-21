<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CustomerAddress;
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
        $validatedData = $request->validate([
            'meal_id' => 'nullable|exists:meals,id',
            'package_id' => 'nullable|exists:packages,id',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        $client_id = Auth::id();
        $meal_id = $validatedData['meal_id'] ?? null;
        $package_id = $validatedData['package_id'] ?? null;
        $qty = $validatedData['qty'];
        $unit_price = $validatedData['unit_price'];
        $shift_id = $validatedData['shift_id'] ?? null;

        // Check if meal or package already exists in the cart
        if ($this->itemExistsInCart($client_id, $meal_id, $package_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item already in cart!',
            ], 400);
        }

        try {
            // Calculate subtotal and insert item into the cart
            $subtotal = $qty * $unit_price;

            DB::table('cart')->insert([
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

            return response()->json(['status' => 'success', 'message' => 'Item added to cart!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add item to cart. Please try again!'], 500);
        }
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
        $validatedData = $request->validate([
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $qty = $validatedData['qty'];
        $unit_price = $validatedData['unit_price'];

        $subtotal = $qty * $unit_price;

        try {
            $query = DB::table('cart')
                ->where('cart.id', $id)
                ->update([
                    'qty' => $qty,
                    'unit_price' => $unit_price,
                    'subtotal' => $subtotal,
                    'updated_at' => Carbon::now(),
                ]);

            return response()->json(['status' => 'success', 'message' => 'Item updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update item. Please try again!'], 500);
        }
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

        try {
            $query = $meal_id ?
                DB::table('cart')->where('client_id', $client_id)->where('meal_id', $meal_id)->delete() :
                DB::table('cart')->where('client_id', $client_id)->where('package_id', $package_id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Item removed from cart successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to remove item. Please try again!'], 500);
        }
    }

    /**
     * Delete cart item
     */
    public function delete($id)
    {
        try {
            $query = DB::table('cart')->where('id', $id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Cart item deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to delete cart item. Please try again!'], 500);
        }
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

        DB::beginTransaction();

        try {
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

            DB::commit();

            // Create the order based on the type (booked/express)
            return $isExpress ? $this->createExpressOrderAction($request) : $this->createBookedOrderAction($request);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error submitting items to cart.'], 500);
        }
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

        $meals = Cart::with(['meal.mealImages'])
            ->where('client_id', $client_id)
            ->whereNull('package_id')
            ->when($isExpress, function ($query) {
                $query->whereNotNull('shift_id');
            }, function ($query) {
                $query->whereNull('shift_id');
            })
            ->where('selected', 0)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal.mealImages'])
            ->where('client_id', $client_id)
            ->whereNotNull('package_id')
            ->when($isExpress, function ($query) {
                $query->whereNotNull('shift_id');
            }, function ($query) {
                $query->whereNull('shift_id');
            })
            ->where('selected', 0)
            ->get();

        $active_address = CustomerAddress::where('location_status', 1)
            ->where('client_id', $client_id)
            ->first();

        $total = $isExpress ? $this->computeExpressSelectedCartTotal() : $this->computeBookedSelectedCartTotal();

        if ($meals->isEmpty() && $packages->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'total' => $total,
                'message' => 'Your cart is empty!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'total' => $total,
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
