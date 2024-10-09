<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CookRating;

class CookRatingController extends Controller
{
    /**
     * Submit a rating for a cook
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rateCook(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'timeliness_rating' => 'nullable|integer|min:1|max:5',
            'courtesy_rating' => 'nullable|integer|min:1|max:5',
            'packaging' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:255',
        ]);

        // Store the rating in the database
        $rating = CookRating::create($validated);

        if ($rating) {
            return response()->json([
                'status' => 'success',
                'message' => 'Rating submitted successfully',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Cook rating was not created',
            ], 500);
        }
    }
}
