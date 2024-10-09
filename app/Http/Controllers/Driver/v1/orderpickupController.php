<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderPickupController extends Controller
{
    /**
     * Get all orders ready for pick-up.
     */
    public function getReadyForPickupOrders()
    {
        $pending_orders = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->select('collections.*', 'orders.order_no', 'cooks.google_map_pin')
            ->where('collections.driver_id', Auth::id())
            ->where('collections.status', 'ready for pickup')
            ->get();

        if ($pending_orders->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $pending_orders,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ]);
    }

    /**
     * Start the order pickup process.
     */
    public function startOrderPickup($orderId)
    {
        $driverId = Auth::id();

        $update = DB::table('collections')
            ->where('order_id', $orderId)
            ->where('driver_id', $driverId)
            ->update(['status' => 'pickup_in_progress']);

        if ($update) {
            return response()->json([
                'status' => 'success',
                'message' => 'Order pickup started successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to start order pickup. Please try again!',
        ]);
    }

    /**
     * Verify OTP and update order status to 'on the way'.
     */
    public function verifyOtpAndUpdateOrder($orderId, Request $request)
    {
        $driverId = Auth::id();
        $inputOtp = $request->input('otp');

        $order = DB::table('orders')
            ->where('id', $orderId)
            ->first();

        if ($order && $order->cook_dely_otp == $inputOtp) {
            DB::table('collections')
                ->where('order_id', $orderId)
                ->where('driver_id', $driverId)
                ->update(['status' => 'on the way']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order picked up successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Incorrect OTP',
        ]);
    }

    /**
     * Get orders ready for delivery to the client.
     */
    public function client_delivery(Request $request)
    {
        $driverId = Auth::id();

        $ready_for_delivery = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->leftJoin('customer_addresses', 'customer_addresses.client_id', '=', 'clients.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->leftJoin(DB::raw('(SELECT meal_id, MIN(id) as image_id FROM meals_images GROUP BY meal_id) as selected_images'), function ($join) {
                $join->on('selected_images.meal_id', '=', 'meals.id');
            })
            ->leftJoin('meals_images', 'meals_images.id', '=', 'selected_images.image_id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->select('collections.*', 'orders.order_no', 'clients.*', 'meals_images.*', 'cooks.*', 'customer_addresses.*')
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'on the way')
            ->get();

        if ($ready_for_delivery->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $ready_for_delivery,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ]);
    }

    /**
     * Verify client's OTP and deliver the order.
     */
    public function clientOtpAndDeliverOrder($orderId, Request $request)
    {
        $driverId = Auth::id();
        $inputOtp = $request->input('otp');

        $order = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->where('collections.order_id', $orderId)
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'on the way')
            ->first();

        if ($order && $order->client_dely_otp == $inputOtp) {
            DB::table('collections')
                ->where('order_id', $orderId)
                ->where('driver_id', $driverId)
                ->update(['status' => 'DELIVERED']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order successfully delivered',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Incorrect OTP',
        ]);
    }

    /**
     * Get delivered orders.
     */
    public function deliveredOrders()
    {
        $delivered_orders = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->select('collections.*', 'orders.order_no', 'cooks.google_map_pin')
            ->where('collections.driver_id', Auth::id())
            ->where('collections.status', 'delivered')
            ->get();

        if ($delivered_orders->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $delivered_orders,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ]);
    }

    /**
     * Display driver analytics.
     */
    public function driverAnalytics()
    {
        $driverId = Auth::id();

        $totalOrders = DB::table('collections')
            ->where('driver_id', $driverId)
            ->count();

        $totalDeliveries = DB::table('collections')
            ->where('driver_id', $driverId)
            ->where('status', 'DELIVERED')
            ->count();

        $averageRating = DB::table('order_ratings')
            ->join('collections', 'order_ratings.order_id', '=', 'collections.order_id')
            ->where('collections.driver_id', $driverId)
            ->avg('order_ratings.driver_rating');

        $deliveryTimeline = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->select('collections.*', 'orders.order_no')
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'DELIVERED')
            ->orderByDesc('collections.updated_at')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_orders' => $totalOrders,
                'total_deliveries' => $totalDeliveries,
                'average_rating' => $averageRating,
                'delivery_timeline' => $deliveryTimeline,
            ],
        ]);
    }
}
