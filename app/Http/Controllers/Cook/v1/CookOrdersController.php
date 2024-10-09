<?php

namespace App\Http\Controllers\Cook\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CookOrdersController extends Controller
{
    /**
     * Get all pending orders for a specific cook.
     */
    public function pending_orders(string $cookId)
    {
        $pending_orders = $this->fetchOrdersByCookAndStatus($cookId, 'Successful Payment');

        return $this->generateResponse($pending_orders, 'Pending Orders');
    }

    /**
     * Mark a specific order as 'Ready for Pickup'.
     */
    public function order_ready(string $orderId)
    {
        $order = $this->fetchOrderByIdAndStatus($orderId, 'Successful Payment');

        if ($order) {
            $updated = DB::table('orders')
                ->where('id', $orderId)
                ->update(['status' => 'Ready for Pickup']);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Order status updated to Ready for Pickup',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order status. Please try again!',
            ], 500);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Order not found or not eligible for status update',
        ], 404);
    }

    /**
     * Get details of a specific order that is marked as 'Successful Payment'.
     */
    public function orders_ready(string $orderId)
    {
        $order = $this->fetchOrderByIdAndStatus($orderId, 'Successful Payment');

        return $this->generateResponse($order, 'Order Details');
    }

    /**
     * Helper method to fetch orders for a cook based on status.
     */
    private function fetchOrdersByCookAndStatus(string $cookId, string $status)
    {
        return DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('cooks.id', $cookId)
            ->where('orders.status', $status)
            ->select('orders.*')
            ->get();
    }

    /**
     * Helper method to fetch a specific order by its ID and status.
     */
    private function fetchOrderByIdAndStatus(string $orderId, string $status)
    {
        return DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->where('orders.id', $orderId)
            ->where('orders.status', $status)
            ->first();
    }

    /**
     * Helper method for generating consistent responses.
     */
    private function generateResponse($data, string $entityName)
    {
        if ($data && !$data->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => "$entityName retrieved successfully",
                'data' => $data,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => "No $entityName found",
        ], 404);
    }
}
