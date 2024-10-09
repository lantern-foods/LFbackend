<?php

namespace App\Http\Controllers\Cook\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageEditRequest;
use App\Http\Requests\PackageRequest;
use App\Models\Cook;
use App\Models\Meal;
use App\Models\Package;
use App\Models\Packagedetail;
use App\Models\PackageMeal;
use App\Models\ShiftPackage;
use App\Traits\Cooks;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use Cooks;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::with('packageMeals.meal.meal_images')->get();

        foreach ($packages as $package) {
            $shift_package = ShiftPackage::where('package_id', $package->id)->first();
            $package->shift_id = $shift_package->shift_id ?? null;
        }

        return response()->json([
            'status' => !$packages->isEmpty() ? 'success' : 'no_data',
            'message' => !$packages->isEmpty() ? 'Request successful' : 'No records found',
            'data' => $packages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create(PackageRequest $request)
    {
        $request->validated();
        $cook = Cook::find($request->input('cook_id'));

        if (!$cook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cook not found',
            ]);
        }

        $meals = $request->input('meals');
        if (empty($meals)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No meals selected',
            ]);
        }

        $package = Package::create([
            'cook_id' => $cook->id,
            'package_name' => $request->input('package_name'),
            'package_description' => $request->input('package_description'),
            'discount' => $request->input('discount'),
            'total_price' => $this->computeMealPrice($meals),
        ]);

        $this->createPackageMeals($meals, $package->id);
        $this->decrementMealsOnCreatePackage($package->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Package created successfully',
            'package' => Package::with('packageMeals.meal')->find($package->id),
        ]);
    }

    /**
     * Edit an existing package.
     */
    public function editPackage(PackageRequest $request)
    {
        $request->validated();

        $package = Package::find($request->input('package_id'));

        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Package not found',
            ]);
        }

        $meals = $request->input('meals');
        if (empty($meals)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No meals selected',
            ]);
        }

        $package->update([
            'package_name' => $request->input('package_name'),
            'package_description' => $request->input('package_description'),
            'discount' => $this->computePackageDiscount($request->input('total_price'), $meals),
            'total_price' => $request->input('total_price'),
        ]);

        $this->createPackageMeals($meals, $package->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Package updated successfully',
            'package' => Package::with('packageMeals.meal')->find($package->id),
        ]);
    }

    /**
     * Compute the total price of the meals.
     */
    protected function computeMealPrice($meals)
    {
        $price = 0;
        foreach ($meals as $meal) {
            $mealInfo = Meal::find($meal['meal_id']);
            if ($mealInfo) {
                $price += $mealInfo->meal_price * $meal['quantity'];
            }
        }
        return $price;
    }

    /**
     * Compute package discount based on total price.
     */
    protected function computePackageDiscount($enteredPrice, $meals)
    {
        $mealPrice = $this->computeMealPrice($meals);
        return max(0, $mealPrice - $enteredPrice);
    }

    /**
     * Create or update meals in the package.
     */
    protected function createPackageMeals($meals, $packageId)
    {
        foreach ($meals as $meal) {
            $packageMeal = PackageMeal::firstOrCreate([
                'meal_id' => $meal['meal_id'],
                'package_id' => $packageId,
            ], ['quantity' => $meal['quantity']]);

            $packageMeal->increment('quantity', $meal['quantity']);
        }
    }

    /**
     * Display a specific package.
     */
    public function show(string $id)
    {
        $package = Package::with('packageMeals.meal.meal_images')->find($id);

        if ($package) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $package,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Package not found!',
        ]);
    }

    /**
     * Update the specified package.
     */
    public function update(PackageEditRequest $request, string $id)
    {
        $request->validated();

        $package = Package::find($id);
        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Package not found',
            ]);
        }

        $package->update([
            'package_name' => $request->input('package_name'),
            'package_description' => $request->input('package_description'),
            'discount' => $request->input('discount'),
            'total_price' => $request->input('total_price'),
        ]);

        $this->updatePackageDetails($request->input('package_details', []), $package->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Package updated successfully',
        ]);
    }

    /**
     * Helper to update package details.
     */
    protected function updatePackageDetails($details, $packageId)
    {
        foreach ($details as $detail) {
            if (isset($detail['id'])) {
                Packagedetail::where('id', $detail['id'])->update(['meal_id' => $detail['meal_id']]);
            }
        }

        $existingIds = array_filter(array_column($details, 'id'));
        Packagedetail::where('package_id', $packageId)->whereNotIn('id', $existingIds)->delete();
    }

    /**
     * Delete a package.
     */
    public function destroy(string $id)
    {
        $package = Package::find($id);
        if ($package) {
            $package->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Package deleted successfully',
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Package not found!',
        ]);
    }
}
