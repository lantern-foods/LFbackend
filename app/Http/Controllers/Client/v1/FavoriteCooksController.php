<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FavoriteCook;
use Illuminate\Support\Facades\Auth ;
use Illuminate\Support\Facades\DB;
class FavoriteCooksController extends Controller
{
    // Add a favorite cook
    public function store(Request $request)
    {
        $request->validate([
            'cook_id' => 'required|integer',
        ]);

        $favorite = new FavoriteCook();
        $favorite->client_id = Auth::id();
        $favorite->cook_id = $request->cook_id;
        

        if ($favorite->save()) {
            # code...
            $data = [
                'status' => 'success',
                'message' => 'Favorite cook added successfully',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, Favorite cook was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    // Get client's favorite cooks
    public function index()
    {
        $favorites_cooks = FavoriteCook::where('client_id', Auth::id())
        // ->with('cook')
        ->orderBy('id', 'desc')
        ->get();
        return response()->json($favorites_cooks);

       if (!$favorites_cooks->isEmpty()) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $favorites_cooks,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }

        return response()->json($data);

    }

    // Delete a favorite cook
    public function destroy($id)
    {
        $favorite = FavoriteCook::where('id', $id)->where('client_id', Auth::id())->first();

        if (!$favorite) {
            if ( $favorite->delete()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Favorite cook deleted successfully',
                ];
            } else {
                // If the delete operation fails for some reason
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Favorite cook was NOT deleted. Please try again!',
                ];
            }
        } else {
            // If no address matches the criteria (not found or doesn't belong to client)
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your address for deletion. Please try again!',
            ];
        }

        return response()->json($data);
    }
}
