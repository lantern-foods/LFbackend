<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderRatingController extends Controller
{
    /**
     * Rate the meal and the driver after the order is delivered.
     *
     * @param  int  $orderId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rateOrder($orderId, Request $request)
    {
        $userId = Auth::id(); // Assuming the user is authenticated and is the client

        // Retrieve the order to ensure it's marked as 'DELIVERED'
        $order = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->where('collections.order_id', $orderId)
            ->where('orders.client_id', $userId) // Assuming there's a client_id in the orders table
            ->where('collections.status', 'DELIVERED')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found or not in the correct status for rating.',
            ]);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'meal_rating' => 'required|integer|min:1|max:5',
            'driver_rating' => 'required|integer|min:1|max:5',
        ]);

        // Store the ratings
        $rateorder = DB::table('order_ratings')->insert([
            'client_id' => $userId,
            'order_id' => $orderId,
            'meal_rating' => $validatedData['meal_rating'],
            'driver_rating' => $validatedData['driver_rating'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($rateorder) {
            $data = [
                'status' => 'success',
                'message' => 'Ratings successfully submitted.',
            ];
        }else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, Ratings was NOT created. Please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Display the ratings submitted by the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showClientRatings()
    {
        $clientId = Auth::id(); // Assuming the client is authenticated

        // Fetch ratings from the 'order_ratings' table, joined with 'orders' to ensure they belong to the current client
        $ratings = DB::table('order_ratings')
            ->join('orders', 'order_ratings.order_id', '=', 'orders.id')
            ->where('orders.client_id', $clientId)
            ->select('order_ratings.*') // Modify this to select specific columns if needed
            ->get();

        // Check if the ratings collection is empty
        if (!$ratings->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $ratings,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }

        return response()->json($data);
    }
}
