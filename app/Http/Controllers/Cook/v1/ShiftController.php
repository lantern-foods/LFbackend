<?php

namespace App\Http\Controllers\Cook\v1;

use App\Http\Controllers\Admin\ShiftAdminController;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftRequest;
use App\Http\Requests\ShiftUpddatRequest;
use App\Models\Cook;
use App\Models\Meal;
use App\Models\Package;
use App\Models\Shift;
use App\Models\ShiftAdminControll;
use App\Models\Shiftmeal;
use App\Models\ShiftPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ShiftController extends Controller
{
    /**
     * List all shifts for the authenticated cook.
     */
    public function index()
    {
        $shifts = Shift::where('cook_id', Auth::id())->get();

        return response()->json([
            'status' => !$shifts->isEmpty() ? 'success' : 'no_data',
            'message' => !$shifts->isEmpty() ? 'Request successful' : 'No records found',
            'data' => $shifts,
        ]);
    }

    /**
     * Get all shifts with their meals for the authenticated cook.
     */
    public function allShiftsmeals()
    {
        $shifts = Shift::where('cook_id', Auth::id())->get();

        foreach ($shifts as $shift) {
            $shift->meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')
                ->where('shift_id', $shift->id)
                ->get();
        }

        return response()->json([
            'status' => !$shifts->isEmpty() ? 'success' : 'no_data',
            'message' => !$shifts->isEmpty() ? 'Request successful' : 'No records found',
            'data' => $shifts,
        ]);
    }

    /**
     * Get shifts for a specific cook.
     */
    public function getShift($cookId)
    {
        $cook = Cook::find($cookId);
        if (!$cook) {
            return response()->json(['error' => 'Cook not found'], 404);
        }

        $shifts = Shift::where('cook_id', $cookId)->get();
        foreach ($shifts as $shift) {
            $shift->meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')
                ->where('shift_id', $shift->id)
                ->get();
        }

        return response()->json($shifts);
    }

    /**
     * Create a new shift with meals and packages.
     */
    public function store(ShiftRequest $request)
    {
        $request->validated();
        $cookId = $request->input('cook_id');
        $meals = $request->input('meals', []);
        $packages = $request->input('packages', []);
        // $startTime = Carbon::parse($request->input('start_time'));
        $startTime = now();

        $endTime = Carbon::parse($request->input('end_time'));

        // Validate presence of meals or packages with a quantity greater than 0
        if (!$this->validateMealsAndPackages($meals, $packages)) {
            return response()->json(['error' => 'Select at least one meal or package with a target greater than 0.'], 400);
        }

        // Ensure shift does not start before the defined start time
        $currentTime = Carbon::now();
        $shiftStatus = $currentTime->lt($startTime) ? 0 : 1; // Scheduled if start time is in the future, otherwise active

        // Create shift
        $shift = Shift::create([
            'cook_id' => $cookId,
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'shift_date' => $request->input('shift_date'),
            'estimated_revenue' => 0, // Initial revenue
            'shift_status' => $shiftStatus, // Scheduled or active
        ]);

        if ($shift) {
            $this->attachMeals($shift, $meals);
            $this->attachPackages($shift, $packages);
            $this->computeEstimateShiftRevenue($shift->id);

            $adminControl = ShiftAdminController::first(); // Ensure this table exists
            $message = 'Shift created successfully.';

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $this->getShiftDetails($shift->id),
            ]);
        }

        // TODO: Implement websockets

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, shift was NOT created. Please try again!',
        ]);
    }

    /**
     * Auto-start shifts based on start time.
     * This function should be called on a schedule (e.g., via a cron job or scheduler).
     */
    public function autoStartShifts()
    {
        $currentTime = Carbon::now();

        // Find all scheduled shifts that have reached their start time
        $shiftsToStart = Shift::where('shift_status', 0) // Scheduled shifts
            ->where('start_time', '<=', $currentTime) // Start time has arrived
            ->get();

        foreach ($shiftsToStart as $shift) {
            $shift->shift_status = 1; // Start the shift
            $shift->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Checked and started all shifts that have reached their start time.',
        ]);
    }

    /**
     * Manually start a shift.
     */
    public function startShiftAction($shiftId)
    {
        $shift = Shift::find($shiftId);
        if (!$shift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Shift not found.',
            ], 404);
        }

        $currentTime = Carbon::now();
        $startTime = Carbon::parse($shift->start_time);

        if ($currentTime->lt($startTime)) {
            // Schedule the shift to start at the specified start time
            $shift->shift_status = 0; // Scheduled
            $shift->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Shift scheduled to start at the defined start time.',
            ]);
        }

        // Start the shift immediately
        $shift->shift_status = 1; // Active
        $shift->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Shift started successfully.',
        ]);
    }

    /**
     * Auto-end shifts based on end time.
     * This function should be called on a schedule (e.g., via a cron job or scheduler).
     */
    public function autoEndShifts()
    {
        $currentTime = Carbon::now();

        // Find all active shifts that have passed their end time
        $shiftsToEnd = Shift::where('shift_status', 1) // Active shifts
            ->where('end_time', '<=', $currentTime) // End time has passed
            ->get();

        foreach ($shiftsToEnd as $shift) {
            $this->endShiftAction($shift->id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Checked and ended all shifts past their end time.',
        ]);
    }

    /**
     * Manually end a shift.
     */
    public function endShiftAction($shiftId)
    {
        $shift = Shift::find($shiftId);
        if ($shift) {
            $shift->shift_status = 2; // End the shift
            $shift->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Shift ended successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Shift not found.',
        ], 404);
    }

    /**
     * Update an existing shift.
     */
    public function update(ShiftUpddatRequest $request, $id)
    {
        $shift = Shift::find($id);
        if (!$shift) {
            return response()->json(['error' => 'Shift not found'], 404);
        }

        $request->validated();

        $meals = $request->input('meals', []);
        $packages = $request->input('packages', []);

        // Validate presence of meals or packages with a quantity greater than 0
        if (!$this->validateMealsAndPackages($meals, $packages)) {
            return response()->json(['error' => 'Select at least one meal or package with a target greater than 0.'], 400);
        }

        $shift->update([
            'end_time' => $request->input('end_time'),
            'shift_date' => $request->input('shift_date'),
            'shift_status' => 1, // Mark as active
        ]);

        $this->attachMeals($shift, $meals);
        $this->attachPackages($shift, $packages);
        $this->computeEstimateShiftRevenue($shift->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift updated successfully.',
            'data' => $this->getShiftDetails($shift->id),
        ]);
    }

    /**
     * Validate that at least one meal or package is selected with a target (quantity) greater than 0.
     */
    private function validateMealsAndPackages($meals, $packages)
    {
        foreach ($meals as $meal) {
            if (isset($meal['quantity']) && $meal['quantity'] > 0) {
                return true;
            }
        }

        foreach ($packages as $package) {
            if (isset($package['quantity']) && $package['quantity'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attach meals to the shift.
     */
    protected function attachMeals(Shift $shift, $meals)
    {
        foreach ($meals as $meal) {
            $this->updateShiftMealIfExistOrAdd($meal, $shift->id);
        }
    }

    /**
     * Attach packages to the shift.
     */
    protected function attachPackages(Shift $shift, $packages)
    {
        foreach ($packages as $package) {
            $this->updateShiftPackageIfExistOrAdd($package, $shift->id);
        }
    }

    /**
     * Get detailed information about a specific shift, including meals and packages.
     */
    private function getShiftDetails($shiftId)
    {
        $shift = Shift::with(['meals', 'packages'])->find($shiftId);
        if (!$shift) {
            return response()->json(['error' => 'Shift not found'], 404);
        }

        $shift->meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')
            ->where('shift_id', $shiftId)
            ->get();

        $shift->packages = ShiftPackage::join('packages', 'shift_packages.package_id', '=', 'packages.id')
            ->where('shift_id', $shiftId)
            ->get();

        foreach ($shift->packages as $package) {
            $package->package_meals = DB::table('package_meals')
                ->join('meals', 'package_meals.meal_id', '=', 'meals.id')
                ->where('package_id', $package->package_id)
                ->get();
        }

        return $shift;
    }

    /**
     * Check if a meal exists for the shift, and update or add the meal.
     */
    protected function updateShiftMealIfExistOrAdd($meal, $shiftId)
    {
        $shiftMeal = Shiftmeal::where('shift_id', $shiftId)->first();
        if ($shiftMeal) {
            $shiftMeal->update(['quantity' => $meal['quantity']]);
        } else {
            Shiftmeal::create([
                'shift_id' => $shiftId,
                'meal_id' => $meal['meal_id'],
                'quantity' => $meal['quantity'],
            ]);
        }
    }

    /**
     * Check if a package exists for the shift, and update or add the package.
     */
    protected function updateShiftPackageIfExistOrAdd($package, $shiftId)
    {
        $shiftPackage = ShiftPackage::where('shift_id', $shiftId)->first();
        if ($shiftPackage) {
            $shiftPackage->update(['quantity' => $package['quantity']]);
        } else {
            ShiftPackage::create([
                'shift_id' => $shiftId,
                'package_id' => $package['package_id'],
                'quantity' => $package['quantity'],
            ]);
        }
    }

    /**
     * Calculate the estimated revenue for the shift based on meals and packages.
     */
    protected function computeEstimateShiftRevenue($shiftId)
    {
        $shift = Shift::find($shiftId);
        $estimatedRevenue = 0;

        // Calculate estimated revenue from meals
        $meals = Shiftmeal::where('shift_id', $shiftId)->get();
        foreach ($meals as $meal) {
            $mealPrice = Meal::where('id', $meal->meal_id)->value('meal_price');
            $estimatedRevenue += $meal->quantity * $mealPrice;
        }

        // Calculate estimated revenue from packages
        $packages = ShiftPackage::where('shift_id', $shiftId)->get();
        foreach ($packages as $package) {
            $packagePrice = Package::where('id', $package->package_id)->value('package_price');
            $estimatedRevenue += $package->quantity * $packagePrice;
        }

        // Update shift with the calculated estimated revenue
        $shift->estimated_revenue = $estimatedRevenue;
        $shift->save();
    }
}
