<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Jobs\SendReassignedDriverOtp;
use App\Models\Collection;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAssignmentController extends Controller
{
    /**
     * Assign orders to a rider (driver).
     */
    public function assignOrdersToRider(Request $request)
    {
        $orderIds = $request->input('orderIds');
        $riderId = $request->input('riderId');
        $assignments = [];

        foreach ($orderIds as $orderId) {
            $order = $this->checkOrderReady($orderId);

            if (!$order) {
                $assignments[] = [
                    'orderId' => $orderId,
                    'status' => 'Order not found or not ready'
                ];
                continue;
            }

            $existingAssignment = Collection::where('order_id', $orderId)->first();

            if ($existingAssignment) {
                $this->sendReassignedOtp($existingAssignment, $riderId);
                $assignments[] = [
                    'orderId' => $orderId,
                    'status' => 'Reassigned, OTP sent'
                ];
                continue;
            }

            $this->assignOrder($orderId, $order->cook_id, $riderId);
            $assignments[] = [
                'orderId' => $orderId,
                'status' => 'Assigned'
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Orders assigned/reassigned successfully',
            'data' => $assignments,
        ]);
    }

    /**
     * Check if an order is ready for assignment.
     */
    protected function checkOrderReady($orderId)
    {
        return DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('orders.status', 'ORDER READY')
            ->where('orders.id', $orderId)
            ->select('orders.*', 'order_details.meal_id', 'meals.cook_id', 'cooks.google_map_pin', 'cooks.kitchen_name')
            ->first();
    }

    /**
     * Assign an order to a rider.
     */
    protected function assignOrder($orderId, $cookId, $riderId)
    {
        Collection::create([
            'order_id' => $orderId,
            'cook_id' => $cookId,
            'driver_id' => $riderId,
        ]);
    }

    /**
     * Send OTP to the reassigned driver and update the collection.
     */
    protected function sendReassignedOtp(Collection $existingAssignment, $newRiderId)
    {
        $driver = Driver::find($newRiderId);
        $otp = mt_rand(100000, 999999);

        $existingAssignment->update([
            'driver_id' => $newRiderId,
            'otp' => $otp,
        ]);

        dispatch(new SendReassignedDriverOtp($driver, $otp));
    }
}
