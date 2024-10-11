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
        // Fetch orders ready for pickup
        $orders = DB::table('orders')
                    ->join('order_details', 'order_details.order_id', '=', 'orders.id')
                    ->join('meals', 'order_details.meal_id', '=', 'meals.id')
                    ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
                    ->where('orders.status', '=', 'ORDER READY')
                    ->select('orders.id as orderId', 'cooks.google_map_pin', 'meals.cook_id')
                    ->get();

        // Assign each order to the nearest rider
        foreach ($orders as $order) {
            $riders = $this->getAvailableRiders();
            [$cookLat, $cookLong] = explode(',', $order->google_map_pin);

            $nearestRiderId = $this->findNearestRider($riders, $cookLat, $cookLong);

            if ($nearestRiderId && $this->canAssignOrder($nearestRiderId)) {
                $this->assignOrderToRider($order->orderId, $order->cook_id, $nearestRiderId);
            }
        }
    }

    /**
     * Retrieve all available riders.
     */
    protected function getAvailableRiders()
    {
        // Fetch all drivers with login status and parse their coordinates
        $riders = DB::table('drivers')
                    ->where('login_status', '=', 1)
                    ->select('id', 'login_location')
                    ->get();

        foreach ($riders as $rider) {
            [$lat, $lon] = explode(',', $rider->login_location);
            $rider->latitude = $lat;
            $rider->longitude = $lon;
        }

        return $riders;
    }

    /**
     * Find the nearest rider to the cook's location.
     */
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

    /**
     * Calculate the Haversine distance between two coordinates.
     */
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

    /**
     * Check if a rider can be assigned more orders.
     */
    protected function canAssignOrder($riderId)
    {
        $assignedOrdersCount = DB::table('collections')
                                 ->where('driver_id', $riderId)
                                 ->where('status', '!=', 'DELIVERED')
                                 ->count();

        return $assignedOrdersCount < 5; // Limit to 5 undelivered orders per rider
    }

    /**
     * Assign the order to the rider.
     */
    protected function assignOrderToRider($orderId, $cookId, $riderId)
    {
        // Check if the order has already been assigned
        $existingAssignment = Collection::where('order_id', $orderId)->first();

        if (!$existingAssignment) {
            // Create a new collection record to assign the order to the rider
            Collection::create([
                'order_id' => $orderId,
                'cook_id' => $cookId,
                'driver_id' => $riderId,
                'status' => 'ready for pickup',
            ]);
        }
    }
}
