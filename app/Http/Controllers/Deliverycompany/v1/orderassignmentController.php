<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Jobs\SendReassignedDriverOtp;
use App\Models\Collection;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;

class OrderAssignmentController extends Controller
{
    public function assignOrdersToRider(Request $request)
    {
        $orderIds = $request->input('orderIds');
        $riderId = $request->input('riderId');
        $assignments = [];

        foreach ($orderIds as $orderId) {
            $order = $this->checkOrderReady($orderId);
            if (!$order) {
                $assignments[] = ['orderId' => $orderId, 'status' => 'Order not found'];
                continue;
            }

            $existingAssignment = Collection::where('order_id', $orderId)->first();

            if ($existingAssignment) {
                $this->sendReassignedOtp($existingAssignment, $riderId);
                $assignments[] = ['orderId' => $orderId, 'status' => 'Reassigned, OTP sent'];
                continue;
            }

            $this->assignOrder($orderId, $order->cook_id, $riderId);
            $assignments[] = ['orderId' => $orderId, 'status' => 'Assigned'];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Driver re-assigned orders successfully',
            'data' => $assignments,
        ]);
    }

    protected function sendReassignedOtp(Collection $existingAssignment, $newRiderId)
    {
        $driver = Driver::find($newRiderId);
        $otp = mt_rand(100000, 999999);

        $existingAssignment->driver_id = $newRiderId;
        $existingAssignment->otp = $otp;
        $existingAssignment->save();

        dispatch(new SendReassignedDriverOtp($driver, $otp));
    }

    protected function checkOrderReady($orderId)
    {
        return DB::table('orders')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('orders.status', '=', 'ORDER READY')
            ->where('orders.id', $orderId)
            ->select('orders.*', 'order_details.meal_id', 'meals.cook_id', 'cooks.google_map_pin', 'cooks.kitchen_name')
            ->first();
    }

    protected function assignOrder($orderId, $cookId, $riderId)
    {
        $collection = new Collection();
        $collection->order_id = $orderId;
        $collection->cook_id = $cookId;
        $collection->driver_id = $riderId;
        $collection->save();
    }
}
