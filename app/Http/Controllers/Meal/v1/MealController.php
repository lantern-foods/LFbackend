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
     * Display a listing of the resource.
     */
    public function all_cook_meals_express()
    {


        $clientId = Auth::user()->id;
        $meals = Meal::with('cook', 'meals_images')
            ->where('express_status', 1)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);
                $meal_rating = MealPackageRating::where('meal_id',$meal->id)->get();
                info($meal_rating);
                return $meal;
            });
        foreach ($meals as $meal) {
            $shift_meal = Shiftmeal::where('meal_id', $meal->id)->first();
            $meal->shift_id = $shift_meal->shift_id ?? null;
        }
        $packages = Package::with([
            'packageMeals.meal.meal_images'
        ])
            ->where('express_status', 1)
            ->get();
        foreach ($packages as $package) {
            $shift_package = ShiftPackage::where('package_id', $package->id)->first();
            $package->shift_id = $shift_package->shift_id ?? null;
        }

        if (!$meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meals,
                'packages' => $packages,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    public function index()
    {


        $clientId = Auth::user()->id;
        $meals = Meal::with('cook', 'meal_images')
            // ->where('express_status', 0)

            ->get()

            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);

                $meal_rating = MealPackageRating::where('meal_id',$meal->id)->get();
                info($meal_rating);
                $meal->packaging = $meal_rating->avg('packaging');
                $meal->taste = $meal_rating->avg('taste');
                $meal->service=  $meal_rating->avg('service');
                $meal->reviews_count = $meal_rating->count('reviews');
                return $meal;
            });
        $packages = Package::with([
            'packageMeals.meal.meal_images'
        ])
            // ->where('express_status', 0)
            ->get();

        if (!$meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meals,
                'packages' => $packages,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function get_meals(string $id)
    {
        $clientId = Auth::user()->id;
        $meal = Meal::with('cook', 'meals_images')
            ->where('id', $id)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);

                // Fetch meal with shift_id
                $shift_meal = Shiftmeal::where('meal_id', $meal->id)->first();
                if ($shift_meal != null) {
                    $meal->shift_id = $shift_meal->shift_id;
                } else {
                    $meal->shift_id = null;
                }
                return $meal;
            });
        // $packages = Package::with([
        //     'packageMeals.meal.meals_images'
        // ])->get();

        if (!$meal->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    public function get_single_cook_meals(string $id)
    {
        $clientId = Auth::user()->id;
        $meal = Meal::with('cook', 'meals_images')
            ->where('id', $id)
            ->get()
            ->map(function ($meal) use ($clientId) {
                $meal->favorites_count = $meal->favorites_count ?? 0;
                $meal->is_liked = $meal->isLikedBy($clientId);

                // Fetch meal with shift_id
                $shift_meal = Shiftmeal::where('meal_id', $meal->id)->first();
                if ($shift_meal != null) {
                    $meal->shift_id = $shift_meal->shift_id;
                } else {
                    $meal->shift_id = null;
                }
                return $meal;
            });
        // $packages = Package::with([
        //     'packageMeals.meal.meals_images'
        // ])->get();

        if (!$meal->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function cook_meals(string $id)
    {
        $cook_meals = Meal::with('meal_images')
            ->where('cook_id', $id)
            ->get();

        if (!$cook_meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $cook_meals,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MealRequest $request)
    {
        $request->validated();

        $cookId = $request->input('cook_id');
        $meal_name = $request->input('meal_name');
        $meal_price = $request->input('meal_price');
        $min_qty = $request->input('min_qty');
        $max_qty = $request->input('max_qty');
        $meal_type = $request->input('meal_type');
        $prep_time = $request->input('prep_time');
        $meal_desc = $request->input('meal_desc');
        $ingredients = $request->input('ingredients');
        $serving_advice = $request->input('serving_advice');

        $meal = Meal::create([
            'cook_id' => $cookId,
            'meal_name' => $meal_name,
            'meal_price' => $meal_price,
            'min_qty' => $min_qty,
            'max_qty' => $max_qty,
            'meal_type' => $meal_type,
            'prep_time' => $prep_time,
            'meal_desc' => $meal_type,
            'ingredients' => $ingredients,
            'serving_advice' => $serving_advice,
            'booked_status' => 1,
            'express_status' => 0,
            'status' => 0,
        ]);

        if ($meal) {
            $data = [
                'status' => 'success',
                'message' => 'Meal created successfully.kindly proceed to upload required images',
                'meal_id' => $meal->id,
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An error occurred.Meal was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Upload meals images
     */
    public function uploadImages(MealImageRequest $request)
    {
        $request->validated();

        $images = $request->file('image_url');
        $meal_id = $request->input('meal_id');
        $savedImageUrls = [];

        foreach ($images as $image) {
            $img = Image::make($image->getRealPath());

            // Determine the image dimensions
            $width = $img->width();
            $height = $img->height();

            // Check if the image needs to be resized
            if ($width != 640 || $height != 480) {
                // Resize the image to 640x480
                $img->resize(882, 484);
            }
            $resizedImageData = $img->encode('png');
            // Define a maximum file size (in bytes)
            $maxFileSize = 5 * 1024 * 1024;  // 5MB (adjust as needed)

            $uniqueFileName = time() . '_' . Str::random(10) . '.png';
            $filePath = 'meals/' . $uniqueFileName;
            $fileSize = strlen($resizedImageData);
            // Convert the image to a binary string
            $fileSizeInKB = $fileSize / 1024;  // Convert to kilobytes
            $fileSizeInMB = $fileSizeInKB / 1024;  // Convert to megabytes

            if ($fileSizeInMB >= 1) {
                // If the file size is 1MB or more, display it in MB
                $formattedFileSize = round($fileSizeInMB, 2) . ' MB';
            } else {
                // Otherwise, display it in KB
                $formattedFileSize = round($fileSizeInKB, 2) . ' KB';
            }

            // Upload the resized image to AWS S3
            $uploaded = $this->uploadToS3($filePath, $resizedImageData);

            if ($uploaded) {
                $savedImageUrls[] = $this->getImageS3Url($filePath);
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'meal photos not uploaded.',
                ];
                return response()->json($data);
            }
        }

        if (!empty($savedImageUrls)) {
            // code...
            foreach ($savedImageUrls as $imageUrl) {
                MealImage::create([
                    'meal_id' => $meal_id,
                    'image_url' => $imageUrl,
                ]);
            }
            $data = [
                'status' => 'success',
                'message' => 'Meal images uploaded successfully. Kindly await approval for the new meal you have created!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'An error occurred. Meal images were NOT uploaded. Please try again!'
            ];
        }
        return response()->json($data);
    }

    private function uploadToS3($filePath, $imageData)
    {
        return Storage::disk('s3')->put($filePath, $imageData);
    }

    private function getImageS3Url($filePath)
    {
        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $meal = Meal::where('id', $id)->first();

        if (!empty($meal)) {
            $data = [
                'status' => 'success',
                'messages' => 'Request successful!',
                'data' => $meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Meal record not found!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MealUpdateRequest $request, string $id)
    {
        $request->validate();

        $meal_price = $request->input('meal_price');
        $min_qty = $request->input('min_qty');
        $max_qty = $request->input('max_qty');

        $meal = Meal::where('id', $id)->first();

        if (!empty($meal)) {
            $meal->meal_price = $meal_price;
            $meal->min_qty = $min_qty;
            $meal->max_qty = $max_qty;

            if ($meal->update()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal updated successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'An error occurred. Meal was NOT updated. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Meal record not found for update. Please try again!',
            ];
        }
    }

    /**
     * Show the form full editing the specified resource.
     */
    public function mealFullEdit(string $id)
    {
        $meal = Meal::where('id', $id)->first();

        if (!empty($meal)) {
            $data = [
                'status' => 'success',
                'messages' => 'Request successful!',
                'data' => $meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Meal record not found!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the full meal.
     */
    public function mealFullUpdate(FullMealUpdateRequest $request, string $id)
    {
        $request->validated();
        //  return $request;
        $meal_name = $request->input('meal_name');
        $meal_price = $request->input('meal_price');
        $min_qty = $request->input('min_qty');
        $max_qty = $request->input('max_qty');
        $meal_type = $request->input('meal_type');
        $prep_time = $request->input('prep_time');
        $meal_desc = $request->input('meal_desc');
        $ingredients = $request->input('ingredients');
        $serving_advice = $request->input('serving_advice');

        $meal = Meal::where('id', $id)->first();

        if (!empty($meal)) {
            $meal->meal_name = $meal_name;
            $meal->meal_price = $meal_price;
            $meal->min_qty = $min_qty;
            $meal->max_qty = $max_qty;
            $meal->meal_type = $meal_type;
            $meal->prep_time = $prep_time;
            $meal->meal_desc = $meal_type;
            $meal->ingredients = $ingredients;
            $meal->serving_advice = $serving_advice;
            $meal->status = 0;

            if ($meal->update()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal updated successfully. Kindly await approval for the changes you have done!',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'AN error occurred. Meal was NOT updated. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Meal record not found for update. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
