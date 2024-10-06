<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;

class orderpickupController extends Controller
{
    /**
     * get all orders ready for pick-up
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

        if (!empty($pending_orders)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $pending_orders,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }

    /**
     * start order pick up process
     */
    public function startOrderPickup($orderId)
    {
        $driverId = Auth::id();

        // Assuming 'pickup_in_progress' is the status indicating the driver is on their way to pick up the order
        $update = DB::table('collections')
            ->where('order_id', $orderId)
            ->where('driver_id', $driverId)
            ->update(['status' => 'pickup_in_progress']);

        if ($update) {
            $data = [
                'status' => 'success',
                'message' => 'Order pickup started',

            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An error occurred. order was NOT updated. Please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * cook order pick-up verification
     */
    public function verifyOtpAndUpdateOrder($orderId, Request $request)
    {
        $driverId = Auth::id();
        $inputOtp = $request->input('otp');

        // Retrieve the order and its associated OTP
        $order = DB::table('orders')
            ->where('id', $orderId)
            ->first();

        // Assuming the OTP is stored in the 'otp' field in the 'collections' table
        if ($order->cook_dely_otp == $inputOtp) {
            // Update the status to 'on the way'
            DB::table('collections')
                ->where('order_id', $orderId)
                ->where('driver_id', $driverId)
                ->update(['status' => 'on the way']);

            $data = [
                'status' => 'success',
                'message' => 'Order picked successful',

            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Incorrect OTP',

            ];
        }
        return response()->json($data);
    }

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
        if (!empty($ready_for_delivery)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $ready_for_delivery,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }

    public function get_client_delivery($orderId)
    {
        $driverId = Auth::id();
        $ready_delivery = DB::table('collections')
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
            ->where('orders.id', $orderId)
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'on the way')
            ->first();
        if (!empty($ready_delivery)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $ready_delivery,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }
    public function clientOtpAndDeliverOrder($orderId, Request $request)
    {
        $driverId = Auth::id();
        $inputOtp = $request->input('otp');

        // Retrieve the order to ensure it's currently marked as 'on the way'
        $order = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->where('collections.order_id', $orderId)
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'on the way')
            ->first();

        // Assuming the customer's OTP is stored in the 'customer_otp' field in the 'collections' table
        if ($order->client_dely_otp == $inputOtp) {
            // Update the status to 'delivered'
            DB::table('collections')
                ->where('order_id', $orderId)
                ->where('driver_id', $driverId)
                ->update(['status' => 'DELIVERED']);
            $data = [
                'status' => 'success',
                'message' => 'Order has been successfully delivered',

            ];

        } else {
            $data = [
                'status' => 'error',
                'message' => 'Incorrect OTP',

            ];
        }
        return response()->json($data);
    }

    public function deliveredOrder($orderId, Request $request)
    {
        $driverId = Auth::id();

        // Retrieve the order to ensure it's currently marked as 'on the way'
        $delivered_order = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('collections.order_id', $orderId)
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'DELIVERED')
            ->first();

        // Assuming the customer's OTP is stored in the 'customer_otp' field in the 'collections' table
        if ($delivered_order) {
            $data = [
                'status' => 'success',
                'message' => 'Request successfully',
                'data' => $delivered_order,

            ];

        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }
        return response()->json($data);
    }
    /**
     * get all delivered orders
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
        if (!empty($delivered_orders)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $delivered_orders,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Display analytics for the driver.
     */
    public function driverAnalytics()
    {
        $driverId = Auth::id();

        // Number of orders
        $totalOrders = DB::table('collections')
            ->where('driver_id', $driverId)
            ->count();

        // Total deliveries
        $totalDeliveries = DB::table('collections')
            ->where('driver_id', $driverId)
            ->where('status', 'DELIVERED')
            ->count();

        // Average rating - Assuming a table 'driver_ratings' with 'driver_id' and 'rating' fields
        $averageRating = DB::table('order_ratings')
            ->join('collections', 'order_ratings.order_id', '=', 'collections.order_id')
            ->where('collections.driver_id', $driverId)
            ->avg('order_ratings.driver_rating');

        // Timeline of deliveries - For simplicity, showing last 5 delivered orders
        $deliveryTimeline = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->select('collections.*', 'orders.order_no')
            ->where('collections.driver_id', $driverId)
            ->where('collections.status', 'DELIVERED')
            ->orderByDesc('collections.updated_at')
            ->limit(5)
            ->get();

        $data = [
            'status' => 'success',
            'data' => [
                'total_orders' => $totalOrders,
                'total_deliveries' => $totalDeliveries,
                'average_rating' => $averageRating,
                'delivery_timeline' => $deliveryTimeline,
            ],
        ];

        return response()->json($data);
    }

}
