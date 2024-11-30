<?php

namespace App\Http\Controllers\Meal\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FullMealUpdateRequest;
use App\Http\Requests\MealImageRequest;
use App\Http\Requests\MealRequest;
use App\Http\Requests\MealUpdateRequest;
use App\Models\Meal;
use App\Models\MealImage;
use App\Models\MealPackageRating;
use App\Models\Package;
use App\Models\Shiftmeal;
use App\Models\ShiftPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class MealController extends Controller
{
    /**
     * Get all express meals and packages.
     */
    public function all_cook_meals_express()
    {
        $clientId = Auth::user()->id;

        $meals = Meal::with('cook', 'mealImages') // Corrected relationship name
            ->where('express_status', 1)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);
                $meal_rating = MealPackageRating::where('meal_id', $meal->id)->get();
                $meal->shift_id = Shiftmeal::where('meal_id', $meal->id)->value('shift_id');

                return $meal;
            });

        $packages = Package::with('packageMeals.meal.mealImages') // Corrected relationship name
            ->where('express_status', 0)
            ->get()
            ->each(function ($package) {
                $package->shift_id = ShiftPackage::where('package_id', $package->id)->value('shift_id');
            });

        if (!$meals->isEmpty() || !$packages->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meals,
                'packages' => $packages,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found',
        ]);
    }

    /**
     * Get all meals and packages.
     */
    public function index()
    {
        $clientId = Auth::user()->id;

        $meals = Meal::with('cook', 'mealImages') // Corrected relationship name
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);
                $meal_rating = MealPackageRating::where('meal_id', $meal->id)->get();
                $meal->packaging = $meal_rating->avg('packaging');
                $meal->taste = $meal_rating->avg('taste');
                $meal->service = $meal_rating->avg('service');
                $meal->reviews_count = $meal_rating->count();
                return $meal;
            });

        $packages = Package::with('packageMeals.meal.mealImages')->get(); // Corrected relationship name

        if (!$meals->isEmpty() || !$packages->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meals,
                'packages' => $packages,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found',
        ]);
    }

    /**
     * Get details of a specific meal.
     */
    public function get_meals(string $id)
    {
        $clientId = Auth::user()->id;

        $meal = Meal::with('cook', 'mealImages') // Corrected relationship name
            ->where('id', $id)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);
                $meal->shift_id = Shiftmeal::where('meal_id', $meal->id)->value('shift_id');
                return $meal;
            });

        if (!$meal->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meal,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found',
        ]);
    }

    /**
     * Get meals from a specific cook.
     */
    public function get_single_cook_meals(string $id)
    {
        $clientId = Auth::user()->id;

        $meal = Meal::with('cook', 'mealImages') // Corrected relationship name
            ->where('id', $id)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);
                $meal->shift_id = Shiftmeal::where('meal_id', $meal->id)->value('shift_id');
                return $meal;
            });

        if (!$meal->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meal,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found',
        ]);
    }

    /**
     * Get all meals from a specific cook.
     */
    public function cook_meals(string $id)
    {
        $cook_meals = Meal::with('mealImages') // Corrected relationship name
            ->where('cook_id', $id)
            ->get();

        if (!$cook_meals->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $cook_meals,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found',
        ]);
    }

    /**
     * Create a new meal.
     */
    public function store(MealRequest $request)
    {
        $request->validated();

        $meal = Meal::create($request->all() + ['booked_status' => 1, 'express_status' => 0, 'status' => 0]);

        if ($meal) {
            return response()->json([
                'status' => 'success',
                'message' => 'Meal created successfully. Proceed to upload required images.',
                'meal_id' => $meal->id,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred. Meal was NOT created. Please try again!',
        ]);
    }

    /**
     * Upload meal images.
     */
    public function uploadImages(MealImageRequest $request)
    {
        $request->validated();

        $meal_id = $request->input('meal_id');
        $savedImageUrls = [];

        foreach ($request->file('image_url') as $image) {
            // Validate that the image is not a GIF
            if ($image->getClientOriginalExtension() === 'gif') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GIF images are not allowed. Please upload other image formats.',
                ], 400);
            }

            // Process the image upload
            $filePath = $this->processImageUpload($image);

            if ($filePath) {
                $savedImageUrls[] = $filePath;
                MealImage::create(['meal_id' => $meal_id, 'image_url' => $filePath]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Meal images were not uploaded. Please try again!',
                ], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Meal images uploaded successfully. Kindly await approval for the new meal!',
        ]);
    }

    /**
     * Process image upload to S3 and return file path.
     *
     * Allow all image types except GIF and resize the image while maintaining quality.
     */
    private function processImageUpload($image)
    {
        // Get the original extension of the uploaded file
        $extension = $image->getClientOriginalExtension();

        // Supported image types except GIF
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];

        // Validate if the image type is allowed
        if (!in_array($extension, $allowedExtensions)) {
            return false; // Invalid image type
        }

        // No need to resize the image, just process the upload and store in S3
        $uniqueFileName = time() . '_' . Str::random(10) . '.' . $extension;
        $filePath = 'meals/' . $uniqueFileName;

        return $this->uploadToS3($filePath, file_get_contents($image)) ? $this->getImageS3Url($filePath) : false;
    }

    private function uploadToS3($filePath, $imageData)
    {
        return Storage::disk('s3')->put($filePath, $imageData, 'public');
    }

    private function getImageS3Url($filePath)
    {
        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Update the specified meal.
     */
    public function update(MealUpdateRequest $request, string $id)
    {
        $meal = Meal::findOrFail($id);
        $meal->fill($request->only(['meal_price', 'min_qty', 'max_qty']));

        if ($meal->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Meal updated successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred. Meal was NOT updated. Please try again!',
        ]);
    }

    /**
     * Update the full meal.
     */
    public function mealFullUpdate(FullMealUpdateRequest $request, string $id)
    {
        $meal = Meal::findOrFail($id);
        $meal->fill($request->all() + ['status' => 0]);

        if ($meal->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Meal updated successfully. Kindly await approval for the changes!',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred. Meal was NOT updated. Please try again!',
        ]);
    }
}
