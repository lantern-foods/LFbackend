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
	 * Calculate order total 
	 */
	public function calcOrderTotal($client_id)
	{
		$order_total = 0;
		return $this->compute;

		// foreach ($order_items as $item_id) {

		// 	$order_item = $this->getCartItem($item_id, $client_id);

		// 	$order_total += $order_item->subtotal;
		// }

		// return $order_total;
	}

	/**
	 *  Get cart item details
	 */
	public function getCartItem($item_id, $client_id)
	{
		$res = Cart::where('client_id', $client_id)->where('id', $item_id)->first();

		return $res;
	}

	protected function computeExpressSelectedCartTotal()
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
			->whereNotNull('package_id',)
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
	protected function computeBookedSelectedCartTotal()
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
	public function mealActionOnCheckout($item_id)
	{
		$client_id = Auth::id();

		$checkout_quantity = Cart::where('client_id', $client_id)
			->where('id', $item_id)
			->value('qty');

		$mealId = Cart::where('client_id', $client_id)
			->where('id', $item_id)->value('meal_id');

		$meal = Shiftmeal::where('meal_id', $mealId);

		if (!$meal->exists()) {
			return;
		}

		$meal_quantity = $meal->value('quantity');
		$updated_qty = $meal_quantity - $checkout_quantity;
		// reduce quantity by 1
		if ($updated_qty >= 0) {
			$meal
				->update(['quantity' => $updated_qty]);
		} else {
			$meal
				->update(['quantity' => 0]);
			// out of stock update package express_status
			Meal::where('id', $mealId)->update(['express_status' => 0, 'booked_status' => 1]);
			// set shift_id to null in cart
			Cart::where('client_id', $client_id)
				->where('id', $item_id)
				->update(['shift_id' => null]);
		}
	}
	public function packageActionOnCheckout($item_id)
	{
		$client_id = Auth::id();
		$checkout_quantity = Cart::where('client_id', $client_id)
			->where('id', $item_id)
			->value('qty');
		$packageId = Cart::where('client_id', $client_id)
			->where('id', $item_id)->value('package_id');
		$package = ShiftPackage::where('package_id', $packageId);
		if (!$package->exists()) {
			return;
		}

		$package_quantity = $package->value('quantity');
		$updated_qty = $package_quantity - $checkout_quantity;
		// reduce quantity by 1
		if ($updated_qty >= 0) {
			$package
				->update(['quantity' => $updated_qty]);
		} else {
			$package
				->update(['quantity' => 0]);
			// out of stock update package express_status
			Package::where('id', $packageId)->update(['express_status' => '0', 'booked_status' => 1]);
			// set shift_id to null in cart
			Cart::where('client_id', $client_id)
				->where('id', $item_id)
				->update(['shift_id' => null]);
		}
	}
	public function createBookedOrderAction($request)
	{
		$client_id = Auth::id();
		$order_no = strtoupper($this->generaterandomString(11));
		// $order_items = $request->input('selected_items');
		$delivery_date = Carbon::parse($request->input('delivery_date'))->format('d-m-Y');
		$delivery_time = $request->input('delivery_time');
		$customer_address = $request->input('address_id');
		$time_requested = $delivery_date . ' ' . $delivery_time;
		$order_total = $this->computeBookedSelectedCartTotal();
		$cook_dely_otp = $otp = mt_rand(100000, 999999); //Generate cook delivery OTP
		$client_dely_otp = $otp = mt_rand(100000, 999999); //Generate rider delivery OTP

		DB::beginTransaction(); //Begin transaction
		$flag = true;

		//Create order master
		$order_id = DB::table('orders')->insertGetId([
			"client_id" => $client_id,
			"order_no" => $order_no,
			"order_type" => 2,
			"dt_req" => $time_requested,
			"order_total" => $order_total,
			"status" => Constants::OTYPE_PENDING_P,
			"cook_dely_otp" => $cook_dely_otp,
			"client_dely_otp" => $client_dely_otp,
			'customeraddress_id' => $customer_address,
			"created_at" => Carbon::now(),
			"updated_at" => Carbon::now(),
		]);

		if (!$order_id) {
			$flag = false;
		}
		// get selected items from cart
		$selected_cart_items = Cart::where('client_id', $client_id)
			->where('selected', 1)
			->pluck('id')
			->toArray();;

		//Save order items

		foreach ($selected_cart_items as $item_id) {

			$order_item = $this->getCartItem($item_id, $client_id);

			$order_line_item = DB::table('order_details')->insert([
				"order_id" => $order_id,
				"meal_id" => $order_item->meal_id,
				"package_id" => $order_item->package_id,
				"qty" => $order_item->qty,
				"unit_price" => $order_item->unit_price,
				"subtotal" => $order_item->subtotal,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
			]);

			if (!$order_line_item) {
				$flag = false;
			}
		}

		//Empty cart
		$empty_cart = DB::table('cart')->where('client_id', $client_id)->delete();

		if (!$empty_cart) {
			$flag = false;
		}

		if ($flag) {
			DB::commit(); //commit transaction

			$data = [
				'status' => 'success',
				'message' => 'Order created successfully!',
				'data' => ['order_id' => $order_id],
			];
		} else {
			DB::rollBack(); //rollback transaction

			$data = [
				'status' => 'error',
				'message' => 'A problem was encountered, order was NOT created. Please try again!',
			];
		}

		return response()->json($data);
	}
	public function createExpressOrderAction($request)
	{
		$client_id = Auth::id();
		$order_no = strtoupper($this->generaterandomString(11));
		// $order_items = $request->input('selected_items');
		$delivery_date = Carbon::parse($request->input('delivery_date'))->format('d-m-Y');
		$delivery_time = $request->input('delivery_time');
		$selected_address_id = $request->input('address_id');
		$customer_address = $selected_address_id ?? Customeraddress::where('client_id', $client_id)->value('id');
		$time_requested = $delivery_date . ' ' . $delivery_time;
		$order_total = $this->computeExpressSelectedCartTotal();
		$cook_dely_otp = $otp = mt_rand(100000, 999999); //Generate cook delivery OTP
		$client_dely_otp = $otp = mt_rand(100000, 999999); //Generate rider delivery OTP

		DB::beginTransaction(); //Begin transaction
		$flag = true;

		//Create order master
		$order_id = DB::table('orders')->insertGetId([
			"client_id" => $client_id,
			"order_no" => $order_no,
			"order_type" => 2,
			"dt_req" => $time_requested,
			"order_total" => $order_total,
			"status" => Constants::OTYPE_PENDING_P,
			"cook_dely_otp" => $cook_dely_otp,
			"client_dely_otp" => $client_dely_otp,
			'customeraddress_id' => $customer_address,
			"created_at" => Carbon::now(),
			"updated_at" => Carbon::now(),
		]);

		if (!$order_id) {
			$flag = false;
		}
		// get selected items from cart
		$selected_cart_items = Cart::where('client_id', $client_id)
			->where('selected', 1)
			->whereNotNull('shift_id')
			->pluck('id')
			->toArray();;

		//Save order items

		foreach ($selected_cart_items as $item_id) {

			$order_item = $this->getCartItem($item_id, $client_id);

			$order_line_item = DB::table('order_details')->insert([
				"order_id" => $order_id,
				"meal_id" => $order_item->meal_id,
				"package_id" => $order_item->package_id,
				"shift_id" => $order_item->shift_id,
				"qty" => $order_item->qty,
				"unit_price" => $order_item->unit_price,
				"subtotal" => $order_item->subtotal,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
			]);

			if (!$order_line_item) {
				$flag = false;
			}
		}

		//Empty express orders in cart
		$empty_cart = DB::table('cart')->where('client_id', $client_id)
			->where('selected', 1)
			->whereNotNull('shift_id')->delete();
		// $empty_cart = DB::table('cart')->where('client_id', $client_id)->delete();

		if (!$empty_cart) {
			$flag = false;
		}

		if ($flag) {
			DB::commit(); //commit transaction

			$data = [
				'status' => 'success',
				'message' => 'Order created successfully!',
				'data' => ['order_id' => $order_id],
			];
		} else {
			DB::rollBack(); //rollback transaction

			$data = [
				'status' => 'error',
				'message' => 'A problem was encountered, order was NOT created. Please try again!',
			];
		}

		return response()->json($data);
	}
	public function updateOrderStatus($order_id, $status)
	{
		$order = Order::find($order_id);
		if ($order) {
			$order->update(['status' => $status]);
		}
	}
}
