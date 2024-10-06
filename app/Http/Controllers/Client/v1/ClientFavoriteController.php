<?php

namespace App\Http\Controllers\Client\v1;



use App\Http\Controllers\Controller;
use App\Models\FavoriteCook;
use App\Models\FavoriteMeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function Laravel\Prompts\error;
use Illuminate\Database\Eloquent\Builder;


class ClientFavoriteController extends Controller
{


    public function add_favourite_meal(Request $request)
    {
        $client_id = auth()->user()->id;

        $meal_id = $request->meal_id;

        $favourite_meal = FavoriteMeal::where('client_id', $client_id)
            ->where('meal_id', $meal_id)
            ->first();

        if ($favourite_meal) {
            return response()->json([
                'message' => 'Meal already added to favourites',
                'status' => 'error'
            ], 400);
        }


        if ($meal_id) {
            $favourite = new FavoriteMeal();

            $favourite->client_id = $client_id;
            $favourite->meal_id = $meal_id;
            $favourite->save();
            return response()->json([
                'message' => 'Meal added to favourites',
                'status' => 'success', 'error' => false,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Meal not found',

                'error' => true
            ], 404);
        }
    }


    public function get_favourite_meals()
    {
        $client_id = auth()->user()->id;

        $favourite_meals = FavoriteMeal::where('client_id', $client_id)
            ->with('meal.meal_images')


            ->get();

        return response()->json([
            'favourite_meals' => $favourite_meals,
            'status' => 'success'
        ], 200);
    }
    public  function  delete_favourite_meal($id)
    {

        $client_id = auth()->user()->id;
        $favourite_meal = FavoriteMeal::where('client_id', $client_id)
            ->where('meal_id', $id);


        $favourite_meal = $favourite_meal->first();
        if ($favourite_meal) {
            $favourite_meal->delete();
            return response()->json([
                'message' => 'Meal deleted from favourites',
                'status' => 'success',
                'error' => false,
            ], 200);
        } else {
            return response()->json([

                'message' => 'Meal not found',
                'status' => 'error', 'error' => true,

            ], 404);
        }
    }
    public  function  add_favourite_cook(Request $request): \Illuminate\Http\JsonResponse
    {
        $client_id = auth()->user()->id;
        $cook_id = $request->cook_id;
        $favourite_cook = FavoriteCook::where('client_id', $client_id)
            ->where('cook_id', $cook_id)
            ->first();
        if ($favourite_cook) {
            return response()->json([
                'message' => 'Cook already added to favourites',
                'status' => 'error'
            ], 400);
        }


        if ($cook_id) {
            $favourite = new FavoriteCook();

            $favourite->client_id = $client_id;
            $favourite->cook_id = $cook_id;
            $favourite->save();
            return response()->json([
                'message' => 'Cook added to favourites',
                'status' => 'success', 'error' => false,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Cook not found',

                'error' => true
            ], 404);
        }
    }
    public  function  get_favourite_cooks(Request $request)
    {

        $client_id = auth()->user()->id;

        $favourite_cooks = FavoriteCook::where('client_id', $client_id)

            ->with('cook')

            ->get();
        return response()->json([
            'favourite_cooks' => $favourite_cooks,
            'status' => 'success'
        ], 200);
    }
    public  function  delete_favourite_cook($id): JsonResponse
    {
        $client_id = auth()->user()->id;
        $favourite_cook = FavoriteCook::where('client_id', $client_id)
            ->where('cook_id', $id);
        $favourite_cook = $favourite_cook->first();



        if ($favourite_cook) {
            $favourite_cook->delete();
            return response()->json([
                'message' => 'Cook deleted from favourites',
                'status' => 'success'
            ], 200);
        } else {
            return response()->json([

                'message' => 'Cook not found',
                'status' => 'error'
            ], 404);
        }
    }
}
