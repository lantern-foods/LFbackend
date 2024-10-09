<?php

namespace App\Http\Controllers;

use App\Models\MealPackageRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealPackageRatingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ratedMeals = MealPackageRating::whereNotNull('meal_id')
            ->with('meal', 'user')
            ->get();

        $ratedPackages = MealPackageRating::whereNotNull('package_id')
            ->with('mealPackage', 'user')
            ->get();

        return response()->json([
            'ratedMeals' => $ratedMeals,
            'ratedPackages' => $ratedPackages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate that either meal_id or package_id is provided, but not both.
        if (is_null($request->package_id) && !isset($request->meal_id)) {
            return response()->json(['message' => 'Please provide an item to rate'], 400);
        }

        if (!is_null($request->package_id) && isset($request->meal_id)) {
            return response()->json(['message' => 'Please provide either a meal or package to rate, not both'], 400);
        }

        // Check if user has already rated the item (meal or package).
        if (!is_null($request->package_id)) {
            $existingRating = MealPackageRating::where('package_id', $request->package_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingRating) {
                return response()->json(['message' => 'You have already rated this package'], 400);
            }
        }

        if (isset($request->meal_id) && is_null($request->package_id)) {
            $existingRating = MealPackageRating::where('meal_id', $request->meal_id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingRating) {
                return response()->json(['message' => 'You have already rated this meal'], 400);
            }
        }

        // Create a new MealPackageRating
        $mealPackageRating = new MealPackageRating();
        $mealPackageRating->user_id = Auth::id();
        $mealPackageRating->meal_id = $request->meal_id;
        $mealPackageRating->package_id = $request->package_id;
        $mealPackageRating->packaging = $request->packaging;
        $mealPackageRating->taste = $request->taste;
        $mealPackageRating->service = $request->service;
        $mealPackageRating->review = $request->review;
        $mealPackageRating->save();

        return response()->json([
            'message' => 'MealPackageRating created successfully',
            'mealPackageRating' => $mealPackageRating,
        ], 201);
    }

    /**
     * Display the ratings for a specific meal.
     */
    public function showMealRatings($meal_id)
    {
        $mealPackageRating = MealPackageRating::where('meal_id', $meal_id)
            ->join('clients', 'clients.id', '=', 'meal_package_ratings.user_id')
            ->select('meal_package_ratings.*', 'clients.full_name')
            ->orderBy('meal_package_ratings.created_at', 'desc')
            ->get();

        return response()->json([
            'meal_ratings' => $mealPackageRating
        ], 200);
    }

    /**
     * Display the ratings for a specific package.
     */
    public function showPackageRatings($package_id)
    {
        $mealPackageRating = MealPackageRating::where('package_id', $package_id)
            ->join('clients', 'clients.id', '=', 'meal_package_ratings.user_id')
            ->select('meal_package_ratings.*', 'clients.full_name')
            ->orderBy('meal_package_ratings.created_at', 'desc')
            ->get();

        return response()->json([
            'package_ratings' => $mealPackageRating
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MealPackageRating $mealPackageRating)
    {
        // Method for showing the form for editing a specific resource.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MealPackageRating $mealPackageRating)
    {
        // Method for updating a specific resource.
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MealPackageRating $mealPackageRating)
    {
        // Method for deleting a specific resource.
    }
}
