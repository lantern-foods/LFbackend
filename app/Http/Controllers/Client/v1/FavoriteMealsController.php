<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FavoriteMeal; // Ensure you have a FavoriteMeal model

use Illuminate\Support\Facades\Auth;

class FavoriteMealsController extends Controller
{
    // Add a favorite meal
    public function store(Request $request)
    {
        $request->validate([
            'meal_id' => 'required|integer',
        ]);

        $favorite = new FavoriteMeal();
        $favorite->client_id = Auth::id();
        $favorite->meal_id = $request->meal_id;

        if ($favorite->save()) {
            $data = [
                'status' => 'success',
                'message' => 'Favorite meal added successfully',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, Favorite meal was NOT created. Please try again!',
            ];
        }

        return response()->json($data);
    }

    // Get client's favorite meals
    public function index()
    {
        $favorites_meals = FavoriteMeal::with('meal.meal_images')
            ->where('client_id', Auth::id())->get();

        if (!$favorites_meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $favorites_meals,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }

        return response()->json($data);
    }

    // Delete a favorite meal
    public function destroy($id)
    {
        $favorite = FavoriteMeal::where('id', $id)->where('client_id', Auth::id())->first();

        if ($favorite) {
            if ($favorite->delete()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Favorite meal deleted successfully',
                ];
            } else {
                // If the delete operation fails for some reason
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Favorite meal was NOT deleted. Please try again!',
                ];
            }
        } else {
            // If no favorite meal matches the criteria (not found or doesn't belong to client)
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate the favorite meal for deletion. Please try again!',
            ];
        }

        return response()->json($data);
    }
}
