<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Customeraddress;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Package;
use App\Models\Shiftmeal;
use App\Models\ShiftPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Orders
{
    /**
     * Calculate order total.
     */
    public function calcOrderTotal(int $client_id): float
    {
        return $this->computeBookedSelectedCartTotal() + $this->computeExpressSelectedCartTotal();
    }

    /**
     * Get cart item details.
     */
    public function getCartItem(int $item_id, int $client_id)
    {
        return Cart::where('client_id', $client_id)->where('id', $item_id)->first();
    }

    /**
     * Compute the total for express-selected items in the cart.
     */
    protected function computeExpressSelectedCartTotal(): float
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal'])
            ->where('client_id', $client_id)
            ->where('package_id', null)
            ->whereNotNull('shift_id')
            ->where('selected', 1)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal'])
            ->where('client_id', $client_id)
            ->whereNotNull('package_id')
            ->whereNotNull('shift_id')
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

    /**
     * Compute the total for booked-selected items in the cart.
     */
    protected function computeBookedSelectedCartTotal(): float
    {
        $client_id = Auth::id();

        $meals = Cart::with(['meal'])
            ->where('client_id', $client_id)
            ->where('package_id', null)
            ->whereNull('shift_id')
            ->where('selected', 1)
            ->get();

        $packages = Cart::with(['package.packageMeals.meal'])
            ->where('client_id', $client_id)
            ->whereNull('shift_id')
            ->whereNotNull('package_id')
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

    /**
     * Handle meal actions on checkout.
     */
    public function mealActionOnCheckout(int $item_id): void
    {
        $client_id = Auth::id();

        $checkout_quantity = Cart::where('client_id', $client_id)
            ->where('id', $item_id)
            ->value('qty');

        $mealId = Cart::where('client_id', $client_id)
            ->where('id', $item_id)
            ->value('meal_id');

        $meal = Shiftmeal::where('meal_id', $mealId);

        if (!$meal->exists()) {
            return;
        }

        $meal_quantity = $meal->value('quantity');
        $updated_qty = $meal_quantity - $checkout_quantity;

        if ($updated_qty >= 0) {
            $meal->update(['quantity' => $updated_qty]);
        } else {
            $meal->update(['quantity' => 0]);
            Meal::where('id', $mealId)->update(['express_status' => 0, 'booked_status' => 1]);
            Cart::where('client_id', $client_id)->where('id', $item_id)->update(['shift_id' => null]);
        }
    }

    /**
     * Handle package actions on checkout.
     */
    public function packageActionOnCheckout(int $item_id): void
    {
        $client_id = Auth::id();

        $checkout_quantity = Cart::where('client_id', $client_id)
            ->where('id', $item_id)
            ->value('qty');

        $packageId = Cart::where('client_id', $client_id)
            ->where('id', $item_id)
            ->value('package_id');

        $package = ShiftPackage::where('package_id', $packageId);

        if (!$package->exists()) {
            return;
        }

        $package_quantity = $package->value('quantity');
        $updated_qty = $package_quantity - $checkout_quantity;

        if ($updated_qty >= 0) {
            $package->update(['quantity' => $updated_qty]);
        } else {
            $package->update(['quantity' => 0]);
            Package::where('id', $packageId)->update(['express_status' => 0, 'booked_status' => 1]);
            Cart::where('client_id', $client_id)->where('id', $item_id)->update(['shift_id' => null]);
        }
    }

    /**
     * Create a booked order.
     */
    public function createBookedOrderAction($request)
    {
        return $this->createOrder($request, 'booked');
    }

    /**
     * Create an express order.
     */
    public function createExpressOrderAction($request)
    {
        return $this->createOrder($request, 'express');
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(int $order_id, string $status): void
    {
        $order = Order::find($order_id);

        if ($order) {
            $order->update(['status' => $status]);
        }
    }

    /**
     * Generic method to create an order (booked or express).
     */
    private function createOrder($request, string $type)
    {
        $client_id = Auth::id();
        $order_no = strtoupper($this->generateRandomString(11));
        $delivery_date = Carbon::parse($request->input('delivery_date'))->format('d-m-Y');
        $delivery_time = $request->input('delivery_time');
        $customer_address = $request->input('address_id') ?? Customeraddress::where('client_id', $client_id)->value('id');
        $time_requested = $delivery_date . ' ' . $delivery_time;
        $order_total = $type === 'express' ? $this->computeExpressSelectedCartTotal() : $this->computeBookedSelectedCartTotal();
        $cook_dely_otp = mt_rand(100000, 999999);
        $client_dely_otp = mt_rand(100000, 999999);

        DB::beginTransaction();

        try {
            // Create order master
            $order_id = DB::table('orders')->insertGetId([
                'client_id' => $client_id,
                'order_no' => $order_no,
                'order_type' => $type === 'express' ? 1 : 2,
                'dt_req' => $time_requested,
                'order_total' => $order_total,
                'status' => Constants::OTYPE_PENDING_P,
                'cook_dely_otp' => $cook_dely_otp,
                'client_dely_otp' => $client_dely_otp,
                'customeraddress_id' => $customer_address,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Save order items
            $selected_cart_items = Cart::where('client_id', $client_id)
                ->where('selected', 1)
                ->when($type === 'express', function ($query) {
                    $query->whereNotNull('shift_id');
                })
                ->pluck('id')
                ->toArray();

            foreach ($selected_cart_items as $item_id) {
                $order_item = $this->getCartItem($item_id, $client_id);

                DB::table('order_details')->insert([
                    'order_id' => $order_id,
                    'meal_id' => $order_item->meal_id,
                    'package_id' => $order_item->package_id,
                    'shift_id' => $order_item->shift_id ?? null,
                    'qty' => $order_item->qty,
                    'unit_price' => $order_item->unit_price,
                    'subtotal' => $order_item->subtotal,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Empty cart for selected items
            Cart::where('client_id', $client_id)
                ->where('selected', 1)
                ->when($type === 'express', function ($query) {
                    $query->whereNotNull('shift_id');
                })
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully!',
                'data' => ['order_id' => $order_id],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'A problem was encountered, order was NOT created. Please try again!',
            ]);
        }
    }
}
