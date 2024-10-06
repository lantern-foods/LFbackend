<?php

namespace App\Http\Controllers\Cook\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

class CookOrdersController extends Controller
{
    public function pending_orders(string $id)
    {
        $pending_orders = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('cooks.id', $id)
            ->select('orders.*')
            ->where('orders.status', 'Successful Payment')
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

    public function order_ready(string $id)
    {
        $pending_order = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('orders.id', $id)
            ->where('orders.status', 'Successful Payment')
            ->first();

        if (!empty($pending_order)) {
            $pending->status = 'Ready for Pickup';

            if ($pending_order->update()) {
                $data = [
                    'status' => 'success',
                    'message' => 'order ready for pick updated successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, your order was NOT updated. Please try again!'
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your order for update. Please try again!'
            ];
        }
        return response()->json($data);
    }

    public function orders_ready(string $id)
    {
        $single_order = DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->where('orders.id', $id)
            ->where('orders.status', 'Successful Payment')
            ->get();

        if (!empty($single_order)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successfully',
                'data' => $single_order
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your order for update. Please try again!'
            ];
        }
        return response()->json($data);
    }
}
