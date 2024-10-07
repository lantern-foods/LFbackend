<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meal;
use App\Models\MealImage;

class MealApprovalController extends Controller
{
    /**
     * Show the meal and its associated images for approval.
     */
    public function edit(string $id)
    {
        $meal = Meal::find($id);

        if ($meal) {
            $mealImages = MealImage::where('meal_id', $id)->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [
                    'meal' => $meal,
                    'meal_images' => $mealImages,
                ],
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Meal record not found!',
        ], 404);
    }

    /**
     * Update the meal approval status.
     */
    public function update(Request $request, string $id)
    {
        // Validate request input
        $request->validate([
            'status' => 'required|string',
        ], [
            'status.required' => 'Approval status is required',
        ]);

        try {
            // Find the meal
            $meal = Meal::findOrFail($id);

            // Update meal status
            $meal->update([
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Meal status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to update meal status. Please try again!',
            ], 500);
        }
    }

    /**
     * Remove the specified meal from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Find the meal
            $meal = Meal::findOrFail($id);

            // Delete the meal images associated with this meal
            MealImage::where('meal_id', $id)->delete();

            // Delete the meal
            $meal->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to delete meal. Please try again!',
            ], 500);
        }
    }
}
