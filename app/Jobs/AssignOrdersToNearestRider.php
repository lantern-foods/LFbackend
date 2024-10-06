<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Collection; 

class AssignOrdersToNearestRider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $orders = DB::table('orders')
                    ->join('order_details', 'order_details.order_id', '=', 'orders.id')
                    ->join('meals', 'order_details.meal_id', '=', 'meals.id')
                    ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
                    ->where('orders.status', '=', 'ORDER READY')
                    ->select('orders.id as orderId', 'cooks.google_map_pin', 'meals.cook_id')
                    ->get();

        foreach ($orders as $order) {
            $riders = $this->getAvailableRiders();
            [$cookLat, $cookLong] = explode(',', $order->google_map_pin);
            $nearestRiderId = $this->findNearestRider($riders, $cookLat, $cookLong);

            if ($nearestRiderId && $this->canAssignOrder($nearestRiderId)) {
                $this->assignOrderToRider($order->orderId, $order->cook_id, $nearestRiderId);
            }
        }
    }

    protected function getAvailableRiders()
    {
        $riders = DB::table('drivers')->where('login_status', '=', 1)->select('id', 'login_location')->get();
        foreach ($riders as $rider) {
            [$lat, $lon] = explode(',', $rider->login_location);
            $rider->latitude = $lat;
            $rider->longitude = $lon;
        }
        return $riders;
    }

    protected function findNearestRider($riders, $cookLat, $cookLong)
    {
        $nearestRiderId = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($riders as $rider) {
            $distance = $this->calculateHaversineDistance($cookLat, $cookLong, $rider->latitude, $rider->longitude);

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $nearestRiderId = $rider->id;
            }
        }

        return $nearestRiderId;
    }

    protected function calculateHaversineDistance($latFrom, $longFrom, $latTo, $longTo)
    {
        $earthRadius = 6371; // Radius of the earth in kilometers
        $latDelta = deg2rad($latTo - $latFrom);
        $longDelta = deg2rad($longTo - $longFrom);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($latFrom)) * cos(deg2rad($latTo)) *
             sin($longDelta / 2) * sin($longDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }

    protected function canAssignOrder($riderId)
    {
        $assignedOrdersCount = DB::table('collections')
                              ->where('driver_id', $riderId)
                              ->where('status', '!=', 'DELIVERED')
                              ->count();

        return $assignedOrdersCount < 5; // Assuming a maximum of 5 undelivered orders per rider
    }

    protected function assignOrderToRider($orderId, $cookId, $riderId)
    {
        $existingAssignment = Collection::where('order_id', $orderId)->first();

        if (!$existingAssignment) {
            $collection = new Collection();
            $collection->order_id = $orderId;
            $collection->cook_id = $cookId;
            $collection->driver_id = $riderId;
            $collection->status = "ready for pickup";
            $collection->save();

        }
    }
}
