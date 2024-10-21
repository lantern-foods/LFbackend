<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FavoriteMeal;
use Illuminate\Support\Facades\Auth;

class FavoriteMealsController extends Controller
{
    /**
     * Add a favorite meal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'meal_id' => 'required|integer',
        ]);

        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');

        // Check if the meal is already added as a favorite
        $existing_favorite = FavoriteMeal::where('client_id', $client_id)
            ->where('meal_id', $meal_id)
            ->first();

        if ($existing_favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meal is already in your favourites.',
            ], 400);
        }

        $favorite = new FavoriteMeal();
        $favorite->client_id = $client_id;
        $favorite->meal_id = $meal_id;

        if ($favorite->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Favorite meal added successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, favorite meal was not added. Please try again!',
        ], 500);
    }

    /**
     * Get all favorite meals for the authenticated client.
     */
    public function index()
    {
        $client_id = Auth::id();
        $favorite_meals = FavoriteMeal::with('meal.mealImages')
            ->where('client_id', $client_id)
            ->get();

        if ($favorite_meals->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No favorite meals found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful.',
            'data' => $favorite_meals,
        ], 200);
    }

    /**
     * Delete a favorite meal.
     */
    public function destroy($id)
    {
        $client_id = Auth::id();
        $favorite = FavoriteMeal::where('id', $id)->where('client_id', $client_id)->first();

        if (!$favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Favorite meal not found or you do not have permission to delete it.',
            ], 404);
        }

        if ($favorite->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Favorite meal deleted successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, favorite meal was not deleted. Please try again!',
        ], 500);
    }
}
