<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Traits\Numbers;
use App\Traits\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    use Numbers, Orders;

    /**
     * Create a booked order.
     */
    public function createOrder(Request $request)
    {
        $this->createBookedOrderAction($request);
    }

    /**
     * Create an express order.
     */
    public function createExpressOrder(Request $request)
    {
        $this->createExpressOrderAction($request);
    }

    /**
     * Get details for a specific order by ID for the authenticated user.
     */
    public function get_order(string $id)
    {
        $client_id = Auth::id();
        $order = DB::table('orders')
            ->where('id', $id)
            ->where('client_id', $client_id)
            ->first();

        if ($order) {
            return response()->json([
                'status' => 'success',
                'message' => 'Order retrieved successfully.',
                'data' => $order,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Order not found. Please try again.',
        ], 404);
    }

    /**
     * Retrieve all orders for the authenticated user based on type (booked or express).
     */
    public function get_orders(Request $request)
    {
        $client_id = Auth::id();
        $type = $request->query('type', 'booked');

        // Determine the query condition based on the order type
        $all_orders = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->where('client_id', $client_id)
            ->when($type === 'booked', function ($query) {
                return $query->whereNull('order_details.shift_id');
            }, function ($query) {
                return $query->whereNotNull('order_details.shift_id');
            })
            ->orderBy('orders.created_at', 'desc')
            ->get();

        if ($all_orders->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully.',
                'data' => $all_orders,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No orders found. Please try again.',
        ], 404);
    }

    /**
     * Get detailed information for a specific order by the authenticated client.
     */
    public function client_order(string $id)
    {
        $client_id = Auth::id();
        $client_order = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('orders.id', $id)
            ->where('orders.client_id', $client_id)
            ->first();

        if ($client_order) {
            return response()->json([
                'status' => 'success',
                'message' => 'Order details retrieved successfully.',
                'data' => $client_order,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Order details not found. Please try again.',
        ], 404);
    }
}
