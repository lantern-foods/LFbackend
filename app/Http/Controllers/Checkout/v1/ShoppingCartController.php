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
    use Numbers;
    use Orders;

    /**
     * View cart
     */
    public function index()
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal.meal_images'])
            ->where('client_id', $client_id)
            ->whereNull('package_id')
            ->where('selected', 0)
            ->whereNull('shift_id')
            ->get();

        $packages = Cart::where('client_id', $client_id)
            ->whereNotNull('package_id')
            ->whereNull('shift_id')
            ->where('selected', 0)
            ->with(['package.packageMeals.meal.meal_images'])
            ->get();

        $active_address = Customeraddress::where('location_status', 1)->where('client_id', $client_id)->first();

        if (!$meals->isEmpty() || !$packages->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'total' => $this->computeCartTotal(),
                'delivery_cost' => 0,
                'address' => $active_address,
                'meals' => $meals,
                'packages' => $packages,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'total' => $this->computeCartTotal(),
                'message' => 'Your cart is empty!',
            ];
        }

        return response()->json($data);
    }

    public function index_express()
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal.meal_images'])
            ->where('client_id', $client_id)
            ->whereNull('package_id')
            ->whereNotNull('shift_id')
            ->where('selected', 0)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal.meal_images'])
            ->where('client_id', $client_id)
            ->whereNotNull('package_id')
            ->whereNotNull('shift_id')
            ->where('selected', 0)
            ->get();

        $active_address = Customeraddress::where('location_status', 1)->where('client_id', $client_id)->first();

        if (!$meals->isEmpty() || !$packages->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'total' => $this->computeCartTotal(),
                'delivery_cost' => 0,
                'address' => $active_address,
                'meals' => $meals,
                'packages' => $packages,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'total' => $this->computeCartTotal(),
                'message' => 'Your cart is empty!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Add to cart
     */
    public function store(Request $request)
    {
        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');
        $qty = $this->clean_monetary_value($request->input('qty'));
        // TODO update the unit price from db
        $unit_price = $this->clean_monetary_value($request->input('unit_price'));
        $package_id = $request->input('package_id');
        $shift_id = $request->input('shift_id');

        if (!isset($meal_id) && !isset($package_id)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid request!',
            ];
            return response()->json($data);
        }
        if (isset($meal_id) && isset($package_id)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid request!. A cart item can either be a meal or a package',
            ];
            return response()->json($data);
        }

        if (!is_numeric($qty)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid quantity!',
            ];

            return response()->json($data);
        } elseif (!is_numeric($unit_price)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid price',
            ];

            return response()->json($data);
        }
        if (Meal::find($meal_id) == null && $package_id == null) {
            $data = [
                'status' => 'error',
                'message' => 'Meal not found',
            ];
            return response()->json($data);
        }
        if (Package::find($package_id) == null && $meal_id == null) {
            $data = [
                'status' => 'error',
                'message' => 'Package not found',
            ];
            return response()->json($data);
        }

        $subtotal = $qty * $unit_price;
        $similar_meal_in_cart = DB::table('cart')
            ->where('client_id', $client_id)
            ->where('meal_id', $meal_id)
            ->first();
        $similar_package_in_cart = DB::table('cart')
            ->where('client_id', $client_id)
            ->where('package_id', $package_id)
            ->first();

        if ($similar_meal_in_cart != null && $meal_id != null) {
            $data = [
                'status' => 'error',
                'message' => 'Meal already in cart!',
            ];
            return response()->json($data);
        }
        if ($similar_package_in_cart != null && $package_id != null) {
            $data = [
                'status' => 'error',
                'message' => 'Package already in cart!',
            ];
            return response()->json($data);
        }
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

        if ($query) {
            $data = [
                'status' => 'success',
                'message' => 'Item added to cart!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An problem was encountered, meal was NOT added to cart. Please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Fetch meal for editing.
     */
    public function edit($id)
    {
        $meal = DB::table('cart')
            ->join('meals', 'cart.meal_id', '=', 'meals.id')
            ->select('meal_id', 'meal_name', 'qty', 'unit_price')
            ->where('cart.id', $id)
            ->first();

        if (!empty($meal)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to retrieve meal for editing, please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Update cart
     */
    public function update(Request $request, $id)
    {
        $qty = $this->clean_monetary_value($request->input('qty'));
        $unit_price = $this->clean_monetary_value($request->input('unit_price'));

        if (!is_numeric($qty)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid quantity!',
            ];

            return response()->json($data);
        } elseif (!is_numeric($unit_price)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid price',
            ];

            return response()->json($data);
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

        if ($query) {
            $data = [
                'status' => 'success',
                'message' => 'Meal updated successfully!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An problem was encountered, meal was NOT updated. Please try again!',
            ];
        }

        return response()->json($data);
    }

    protected function updateCartTotal()
    {
        $client_id = Auth::id();
        $total = $this->computeCartTotal();
        $query = DB::table('carts')->where('client_id', $client_id)->update(['total' => $total]);
        return $query;
    }

    protected function computeCartTotal()
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal'])
            ->where('client_id', $client_id)
            ->where('package_id', null)
            ->where('selected', 0)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal'])
            ->where('client_id', $client_id)
            ->where('package_id', '!=', null)
            ->where('selected', 0)
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

    protected function computeSelectedCartTotal()
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal'])
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
    /** Remove from cart */

    // protected function
    public function removeFromCart(Request $request)
    {
        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');
        $package_id = $request->input('package_id');

        // check if meal_id is not null

        if (!isset($meal_id) && !isset($package_id)) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid request!',
            ];
            return response()->json($data);
        }
        if (!isset($meal_id)) {
            $query = DB::table('cart')->where('client_id', $client_id)->where('meal_id', $meal_id)->delete();
        }
        if (!isset($package_id)) {
            $query = DB::table('cart')->where('client_id', $client_id)->where('package_id', $package_id)->delete();
        }

        if ($query) {
            $data = [
                'status' => 'success',
                'message' => 'Item removed from cart successfully!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An problem was encountered, item was NOT removed from cart. Please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Delete from cart
     */
    public function delete($id)
    {
        $query = DB::table('cart')->where('cart.id', $id)->delete();

        if ($query) {
            $data = [
                'status' => 'success',
                'message' => 'Cart item deleted successfully!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An problem was encountered, cart item was NOT deleted. Please try again!',
            ];
        }

        return response()->json($data);
    }

    public function submitFinalCartData(Request $request)
    {
        $client_id = Auth::id();
        $address_id = $request->input('address_id');
        $delivery_date = $request->input('delivery_date');
        $delivery_time = $request->input('delivery_time');
        $payment_method = $request->input('order_note');
        $meals_to_update = $request->input('meals', []);
        $packages_to_update = $request->input('packages', []);
        if (!Cart::where('client_id', $client_id)->exists()) {
            $data = [
                'status' => 'error',
                'message' => 'Cart is empty!',
            ];
            return response()->json($data);
        }
        if ($packages_to_update == [] && $meals_to_update == []) {
            $data = [
                'status' => 'error',
                'message' => 'Please select items to checkout',
            ];
            return response()->json($data);
        }

        foreach ($meals_to_update as $meal) {
            DB::table('cart')
                ->where('client_id', $client_id)
                ->where('id', $meal['cart_meal_item_id'])
                ->update(['selected' => 1, 'qty' => $meal['final_quantity'], 'subtotal' => $this->updateCartItemSubtotal($meal['cart_meal_item_id'], $meal['final_quantity'])]);
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
        }

        $create_order_res = $this->createBookedOrderAction($request);

        return $create_order_res;
    }

    public function submitFinalCartDataExpress(Request $request)
    {
        $client_id = Auth::id();

        $address_id = $request->input('address_id');
        $delivery_date = $request->input('delivery_date');
        $delivery_time = $request->input('delivery_time');
        $payment_method = $request->input('order_note');
        $meals_to_update = $request->input('meals', []);
        $packages_to_update = $request->input('packages', []);
        if (!Cart::where('client_id', $client_id)->exists()) {
            $data = [
                'status' => 'error',
                'message' => 'Cart is empty!',
            ];
            return response()->json($data);
        }
        foreach ($meals_to_update as $meal) {
            DB::table('cart')
                ->where('client_id', $client_id)
                ->where('id', $meal['cart_meal_item_id'])
                ->update(['selected' => 1, 'qty' => $meal['final_quantity'], 'subtotal' => $this->updateCartItemSubtotal($meal['cart_meal_item_id'], $meal['final_quantity'])]);
            $this->mealActionOnCheckout($meal['cart_meal_item_id']);
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
            $this->packageActionOnCheckout($package['cart_package_item_id']);
        }
        // $data = [

        //     'status' => 'success',
        //     'message' => 'Cart items updated successfully!',
        // ];

        $create_order_res = $this->createExpressOrderAction($request);

        return $create_order_res;
    }

    private function updateCartItemSubtotal($id, $qty)
    {
        $cart_item = Cart::find($id);
        $subtotal = $qty * $cart_item->unit_price;
        return $subtotal;
    }

    public function updateAddressStatus(Request $request, string $id)
    {
        $client_id = Auth::id();

        // First, set all addresses for the client to location_status = 0
        Customeraddress::where('client_id', $client_id)->update(['location_status' => 0]);

        // Then, find the specific address by ID and set its location_status to 1
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if (!empty($customer_address)) {
            $customer_address->location_status = 1;

            if ($customer_address->save()) {  // Using save() since only one attribute is being updated
                $data = [
                    'status' => 'success',
                    'message' => 'Address status updated successfully. All other addresses have been set to inactive.',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Address status was NOT updated. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your address for status update. Please try again!',
            ];
        }

        return response()->json($data);
    }
}
