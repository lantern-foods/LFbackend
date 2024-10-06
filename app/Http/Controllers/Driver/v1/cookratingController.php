<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CookRating;

class cookratingController extends Controller
{
    public function rateCook(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'timeless_rating' => 'nullable|integer|min:1|max:5',
            'courtesy_rating' => 'nullable|integer|min:1|max:5',
            'packaging' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = CookRating::create($validated);
        if ($rating) {
            $data = [
                'status' => 'success',
                'message' => 'Rating submitted successfully'
            ];
        }else {
            
            $data = [
                'status' => 'error',
                'message' => 'An error occurred.Cook rating was NOT Created'
            ];
        }

        return response()->json($data);
    }
}
