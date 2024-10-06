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

        if (!$packages->isEmpty()) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $packages,
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
    protected function computeMealPrice($meals)
    {
        $price = 0;
        foreach ($meals as $meal) {

            $mealId = $meal['meal_id'];
            $quantity = $meal['quantity'];
            $mealInfo = Meal::find($mealId);
            if ($meal) {
                $price += $mealInfo->meal_price * $quantity;
            }
        }
        return $price;
    }
    protected function computePackageDiscount($enteredPrice, $meals)
    {

        $discount = $this->computeMealPrice($meals) - $enteredPrice;
        if ($discount < 0) {
            $discount = 0;
        }
        return  $discount;
    }
    protected function createPackageMeal($meals, $packageId)
    {

        foreach ($meals as $meal) {
            $packageMeal = PackageMeal::where('meal_id', $meal['meal_id'])->where('package_id', $packageId)->first();
            if (empty($packageMeal)) {
                PackageMeal::create([
                    'meal_id' => $meal['meal_id'],
                    'quantity' => $meal['quantity'],
                    'package_id' => $packageId,
                ]);
            } else {
                $packageMeal->quantity += $meal['quantity'];
                $packageMeal->save();
            }
        }
    }
    public function create(PackageRequest $request)
    {
        $request->validated();
        $cookId = $request->input('cook_id');
        $packageName = $request->input('package_name');
        $packageDescription = $request->input('package_description');
        $discount = $request->input('discount');
        $totalPrice = $request->input('total_price');


        $cook = Cook::where('id', $cookId)->first();
        if (empty($cook)) {
            $data = [
                'status' => 'error',
                'message' => 'Cook not found',
            ];
            return response()->json($data);
        }
        $meals = $request->input('meals');
        if (empty($meals)) {
            $data = [
                'status' => 'error',
                'message' => 'No meals selected',
            ];


            return response()->json($data);
        }
        $package = Package::create([
            'cook_id' => $cookId,
            'package_name' => $packageName,
            'package_description' => $packageDescription,
            'discount' => $discount,
            'total_price' => $this->computeMealPrice($meals),

        ]);

        $packageId = $package->id;;


        $this->createPackageMeal($meals, $packageId);
        $this->decrementMealsOnCreatePackage($packageId);
        $packageWithMeals = Package::with('packageMeals.meal')->find($packageId);
        $data = [
            'status' => 'success',
            'message' => 'Package created successfully',
            'package' => $packageWithMeals,
        ];
        return response()->json($data);
    }
    public function editPackage(PackageRequest $request)
    {
        $request->validated();
        $packageId = $request->input('package_id');
        $packageName = $request->input('package_name');
        $packageDescription = $request->input('package_description');
        $discount = $request->input('discount');
        $totalPrice = $request->input('total_price');

        $package = Package::find($packageId);
        if (empty($package)) {
            $data = [
                'status' => 'error',
                'message' => 'Package not found',
            ];
            return response()->json($data);
        }

        $meals = $request->input('meals');
        if (empty($meals)) {
            $data = [
                'status' => 'error',
                'message' => 'No meals selected',
            ];


            return response()->json($data);
        }

        $package->package_name = $packageName;
        $package->package_description = $packageDescription;
        $package->discount
            = $this->computePackageDiscount($totalPrice, $meals);
        $package->total_price = $totalPrice;
        $package->save();

        $meals = $request->input('meals');
        if (!empty($meals)) {
            $this->createPackageMeal($meals, $packageId);
        }

        $packageWithMeals = Package::with('packageMeals.meal')->find($packageId);
        $data = [
            'status' => 'success',
            'message' => 'Package updated successfully',
            'package' => $packageWithMeals,
        ];
        return response()->json($data);
    }


    public function store(PackageRequest $request)
    {
        $request->validated();

        // Extract the required inputs
        $cookId = $request->input('cook_id');
        $packageName = $request->input('package_name');
        $packageDescription = $request->input('package_description');
        $discount = $request->input('discount');
        $totalPrice = $request->input('total_price');

        // Create a new package record
        $package = Package::create([
            'cook_id' => $cookId,
            'package_name' => $packageName,
            'package_description' => $packageDescription,
            'discount' => $discount,
            'total_price' => $totalPrice,
        ]);

        // Get the ID of the newly created package
        $packageId = $package->id;


        // Retrieve the package details from the request
        $packageDetails = $request->input('package_details');
        if (is_array($packageDetails)) {
            foreach ($packageDetails as $detail) {
                // Assume $detail is an array that contains 'meal_id'.
                // Make sure to validate or sanitize this input as needed.
                PackageMeal::create([
                    'package_id' => $packageId,
                    'meal_id' => $detail['meal_id'],
                ]);
            }
            $data = [
                'status' => 'success',
                'message' => 'Package created successfully',

            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, account was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $package = Package::where('id', $id)->first();

        if (!empty($package)) {

            $package_details = Packagedetail::where('package_id', $id)->get();

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [
                    'package' => $package, // Include the package data
                    'package_details' => $package_details, // Include the package details
                ],
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to view packages. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $package = Package::where('id', $id)->first();

        if (!empty($package)) {

            $package_details = Packagedetail::where('package_id', $id)->get();

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [
                    'package' => $package, // Include the package data
                    'package_details' => $package_details, // Include the package details
                ],
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your package data. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackageEditRequest $request, string $id)
    {
        $request->validated();

        $packageName = $request->input('package_name');
        $packageDescription = $request->input('package_description');
        $discount = $request->input('discount');
        $totalPrice = $request->input('total_price');
        $packageDetailsArray = $request->input('package_details', []);

        $package = Package::where('id', $id)->first();

        if (!empty($package)) {

            $package->package_name = $packageName;
            $package->package_description = $packageDescription;
            $package->discount = $discount;
            $package->total_price = $totalPrice;

            if ($package->update()) {
                // Update existing package details
                foreach ($packageDetailsArray as $detail) {
                    if (isset($detail['id'])) {
                        // Update existing detail
                        Packagedetail::where('id', $detail['id'])->update(['meal_id' => $detail['meal_id']]);
                    }
                }

                $detailIds = array_filter(array_column($packageDetailsArray, 'id'));
                Packagedetail::where('package_id', $id)->whereNotIn('id', $detailIds)->delete();

                $data = [

                    'status' => 'success',
                    'message' => 'Package updated successfully',
                ];
            } else {

                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, your package was NOT updated. Please try again!'
                ];
            }
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your package for update. Please try again!'
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
