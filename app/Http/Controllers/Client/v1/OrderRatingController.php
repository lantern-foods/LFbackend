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
        $clientId = Auth::id(); // Assuming the user is authenticated

        // Retrieve the order to ensure it's marked as 'DELIVERED'
        $order = DB::table('collections')
            ->join('orders', 'collections.order_id', '=', 'orders.id')
            ->where('collections.order_id', $orderId)
            ->where('orders.client_id', $clientId)
            ->where('collections.status', 'DELIVERED')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found or not eligible for rating.',
            ], 404);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'meal_rating' => 'required|integer|min:1|max:5',
            'driver_rating' => 'required|integer|min:1|max:5',
        ]);

        // Insert the ratings into 'order_ratings' table
        $rateOrder = DB::table('order_ratings')->insert([
            'client_id' => $clientId,
            'order_id' => $orderId,
            'meal_rating' => $validatedData['meal_rating'],
            'driver_rating' => $validatedData['driver_rating'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($rateOrder) {
            return response()->json([
                'status' => 'success',
                'message' => 'Ratings submitted successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred, ratings were not submitted. Please try again.',
        ], 500);
    }

    /**
     * Display the ratings submitted by the authenticated client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showClientRatings()
    {
        $clientId = Auth::id(); // Assuming the client is authenticated

        // Fetch ratings from 'order_ratings', joined with 'orders' to ensure they belong to the client
        $ratings = DB::table('order_ratings')
            ->join('orders', 'order_ratings.order_id', '=', 'orders.id')
            ->where('orders.client_id', $clientId)
            ->select('order_ratings.*') // Modify this to select specific columns if needed
            ->get();

        if ($ratings->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No ratings found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful.',
            'data' => $ratings,
        ], 200);
    }
}
