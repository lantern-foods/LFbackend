<?php

namespace App\Http\Controllers;

use App\Models\MealPackageRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealPackageRatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
  $ratedMeals = MealPackageRating::

      whereNotNull('meal_id')
      ->
      with('meal')
      ->with('user')

      ->get();
  $ratedPackages = MealPackageRating::

      whereNotNull('package_id')
      ->
      with('mealPackage')
      ->with('user')

      ->get();
  return response()->json(['ratedMeals' => $ratedMeals, 'ratedPackages' => $ratedPackages]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->package_id==null && !isset($request->meal_id)){
                   return  response()->json(['message' => 'Please provide an item to rate'], 400);
                }
                if($request->package_id!=null && isset($request->meal_id)){
                    return  response()->json(['message' => 'Please provide an item to rate'], 400);
                }
                if($request->package_id!=null && !isset($request->meal_id)){
                    $mealPackageRating = MealPackageRating::where('package_id', $request->package_id)
                        ->where('user_id', Auth::id())
                        ->first();
                    if ($mealPackageRating) {
                        return response()->json(['message' => 'You have already rated this package'], 400);
                    }
                }
                if(isset($request->meal_id) && !isset($request->package_id)){
                    $mealPackageRating = MealPackageRating::where('meal_id', $request->meal_id)
                        ->where('user_id', Auth::id())
                        ->first();
                    if ($mealPackageRating) {
                        return response()->json(['message' => 'You have already rated this meal'], 400);
                    }
                }
        // Create a new MealPackageRating
        // Validate the request data
        $mealPackageRating = new MealPackageRating();
        $mealPackageRating->user_id = Auth::id();
        $mealPackageRating->meal_id = $request->meal_id;
        $mealPackageRating->package_id = $request->package_id;
        $mealPackageRating->packaging = $request->packaging;
        $mealPackageRating->taste = $request->taste;
        $mealPackageRating->service = $request->service;
        $mealPackageRating->review = $request->review;
        $mealPackageRating->save();
        return response()->json(['message' => 'MealPackageRating created successfully', 'mealPackageRating' => $mealPackageRating], 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function showMealRatings(Request $request,$meal_id)
    {
        //

        $mealPackageRating = MealPackageRating::where('meal_id', $meal_id)
            ->join('clients', 'clients.id', '=', 'meal_package_ratings.user_id')
            ->select('meal_package_ratings.*', 'clients.full_name')
            ->orderBy('meal_package_ratings.created_at', 'desc') // Order by created_at in descending order
            ->get();

        return response()->json([

            'meal_ratings' => $mealPackageRating], 200);

    }
    public function showPackageRatings(Request $request, $package_id)
    {
        //
        $mealPackageRating = MealPackageRating::where('package_id', $package_id)
            ->join('clients', 'clients.id', '=', 'meal_package_ratings.user_id')
            ->select('meal_package_ratings.*', 'clients.full_name')
            ->orderBy('meal_package_ratings.created_at', 'desc') // Order by created_at in descending order
            ->get();
        return response()->json(['package_ratings' => $mealPackageRating], 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MealPackageRating $mealPackageRating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MealPackageRating $mealPackageRating)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MealPackageRating $mealPackageRating)
    {
        //
    }
}
