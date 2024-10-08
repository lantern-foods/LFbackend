<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FavoriteCook;
use Illuminate\Support\Facades\Auth;

class FavoriteCooksController extends Controller
{
    /**
     * Add a favorite cook.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cook_id' => 'required|integer',
        ]);

        $client_id = Auth::id();
        $cook_id = $request->input('cook_id');

        // Check if the cook is already added as a favorite
        $existing_favorite = FavoriteCook::where('client_id', $client_id)
            ->where('cook_id', $cook_id)
            ->first();

        if ($existing_favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cook is already in your favourites.',
            ], 400);
        }

        $favorite = new FavoriteCook();
        $favorite->client_id = $client_id;
        $favorite->cook_id = $cook_id;

        if ($favorite->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Favorite cook added successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, favorite cook was not added. Please try again!',
        ], 500);
    }

    /**
     * Get all favorite cooks for the authenticated client.
     */
    public function index()
    {
        $client_id = Auth::id();
        $favorite_cooks = FavoriteCook::where('client_id', $client_id)
            ->with('cook') // Assuming you want to include cook details
            ->orderBy('id', 'desc')
            ->get();

        if ($favorite_cooks->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No favorite cooks found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful.',
            'data' => $favorite_cooks,
        ], 200);
    }

    /**
     * Delete a favorite cook.
     */
    public function destroy($id)
    {
        $client_id = Auth::id();
        $favorite = FavoriteCook::where('id', $id)->where('client_id', $client_id)->first();

        if (!$favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Favorite cook not found or you do not have permission to delete it.',
            ], 404);
        }

        if ($favorite->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Favorite cook deleted successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, favorite cook was not deleted. Please try again!',
        ], 500);
    }
}
