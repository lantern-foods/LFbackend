<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClientRating;

class clientratingController extends Controller
{
    public function rateClient(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'timeless_rating' => 'nullable|integer|min:1|max:5',
            'courtesy_rating' => 'nullable|integer|min:1|max:5',
            'delivery_directions' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = ClientRating::create($validated);
        if ($rating) {
            $data = [
                'status' => 'success',
                'message' => 'Rating submitted successfully'
            ];
        }else {
            
            $data = [
                'status' => 'error',
                'message' => 'An error occurred.Client rating was NOT Created'
            ];
        }

        return response()->json($data);
    }
}
