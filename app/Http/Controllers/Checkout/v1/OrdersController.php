<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customeraddress;
use App\Traits\Numbers;
use App\Traits\Orders;
use App\Traits\Constants;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\TextUI\Configuration\Constant;

class OrdersController extends Controller
{
    use Numbers, Orders;

    /**
     * Create order
     */
    // BOOKED ORDER
    public function createOrder(Request $request)
    {
        $this->createBookedOrderAction($request);
    }
    public function createExpressOrder(Request $request)
    {
        $this->createExpressOrderAction($request);
    }


    public function get_order(string $id)
    {
        $client_id = Auth::id();
        $pending_orders = DB::table('orders')
            ->where('id', $id)
            ->where('client_id', $client_id)
            // ->where("status", '=', "Pending Payment")
            ->first();


        if (!empty($pending_orders)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $pending_orders,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your order. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function get_orders(Request $request)
    {
        $client_id = Auth::id();
        $type = $request->query('type','booked');
      if($type =='booked'){
          $all_orders = DB::table('orders')
              ->join('order_details', 'order_details.order_id', '=', 'orders.id')
              ->where('client_id', $client_id)
//              where order_details.shift_id,'==',null
                  ->whereNull('order_details.shift_id')


              ->orderBy('orders.created_at', 'desc')
              ->get();
      }else{
          $all_orders = DB::table('orders')
              ->join('order_details', 'order_details.order_id', '=', 'orders.id')
              ->where('client_id', $client_id)
              ->whereNotNull('order_details.shift_id')
              ->orderBy('orders.created_at', 'desc')
              ->get();
      }

        if (!empty($all_orders)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $all_orders,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your order. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function client_order(string $id)
    {
        $client_id = Auth::id();
        $client_orders = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('orders.id', $id)
            ->where('orders.client_id', $client_id)
            ->first();

        if (!empty($client_orders)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $client_orders,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your order. Please try again!',
            ];
        }
        return response()->json($data);
    }
}
