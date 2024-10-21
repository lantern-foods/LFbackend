<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\FavoriteCook;
use App\Models\FavoriteMeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientFavoriteController extends Controller
{
    /**
     * Add meal to favourites
     */
    public function add_favourite_meal(Request $request): JsonResponse
    {
        $client_id = Auth::id();
        $meal_id = $request->input('meal_id');

        if (!$meal_id) {
            return response()->json([
                'message' => 'Meal ID is required.',
                'status' => 'error'
            ], 400);
        }

        // Check if the meal is already in favourites
        $favourite_meal = FavoriteMeal::where('client_id', $client_id)
            ->where('meal_id', $meal_id)
            ->first();

        if ($favourite_meal) {
            return response()->json([
                'message' => 'Meal already added to favourites.',
                'status' => 'error'
            ], 400);
        }

        // Add to favourites
        FavoriteMeal::create([
            'client_id' => $client_id,
            'meal_id' => $meal_id
        ]);

        return response()->json([
            'message' => 'Meal added to favourites.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Get all favourite meals for the authenticated client
     */
    public function get_favourite_meals(): JsonResponse
    {
        $client_id = Auth::id();

        $favourite_meals = FavoriteMeal::where('client_id', $client_id)
            ->with('meal.mealImages')
            ->get();

        return response()->json([
            'favourite_meals' => $favourite_meals,
            'status' => 'success'
        ], 200);
    }

    /**
     * Remove meal from favourites
     */
    public function delete_favourite_meal($id): JsonResponse
    {
        $client_id = Auth::id();

        // Find the favourite meal
        $favourite_meal = FavoriteMeal::where('client_id', $client_id)
            ->where('meal_id', $id)
            ->first();

        if (!$favourite_meal) {
            return response()->json([
                'message' => 'Meal not found in favourites.',
                'status' => 'error'
            ], 404);
        }

        // Delete the favourite meal
        $favourite_meal->delete();

        return response()->json([
            'message' => 'Meal deleted from favourites.',
            'status' => 'success'
        ], 200);
    }

    /**
     * Add cook to favourites
     */
    public function add_favourite_cook(Request $request): JsonResponse
    {
        $client_id = Auth::id();
        $cook_id = $request->input('cook_id');

        if (!$cook_id) {
            return response()->json([
                'message' => 'Cook ID is required.',
                'status' => 'error'
            ], 400);
        }

        // Check if the cook is already in favourites
        $favourite_cook = FavoriteCook::where('client_id', $client_id)
            ->where('cook_id', $cook_id)
            ->first();

        if ($favourite_cook) {
            return response()->json([
                'message' => 'Cook already added to favourites.',
                'status' => 'error'
            ], 400);
        }

        // Add to favourites
        FavoriteCook::create([
            'client_id' => $client_id,
            'cook_id' => $cook_id
        ]);

        return response()->json([
            'message' => 'Cook added to favourites.',
            'status' => 'success',
        ], 200);
    }

    /**
     * Get all favourite cooks for the authenticated client
     */
    public function get_favourite_cooks(): JsonResponse
    {
        $client_id = Auth::id();

        $favourite_cooks = FavoriteCook::where('client_id', $client_id)
            ->with('cook')
            ->get();

        return response()->json([
            'favourite_cooks' => $favourite_cooks,
            'status' => 'success'
        ], 200);
    }

    /**
     * Remove cook from favourites
     */
    public function delete_favourite_cook($id): JsonResponse
    {
        $client_id = Auth::id();

        // Find the favourite cook
        $favourite_cook = FavoriteCook::where('client_id', $client_id)
            ->where('cook_id', $id)
            ->first();

        if (!$favourite_cook) {
            return response()->json([
                'message' => 'Cook not found in favourites.',
                'status' => 'error'
            ], 404);
        }

        // Delete the favourite cook
        $favourite_cook->delete();

        return response()->json([
            'message' => 'Cook deleted from favourites.',
            'status' => 'success'
        ], 200);
    }
}
