<?php

namespace App\Http\Controllers\Cook\v1;

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

        // Validate presence of meals or packages
        if (empty($meals) && empty($packages)) {
            return response()->json(['error' => 'At least one meal or package is required.'], 400);
        }

        // Create shift
        $shift = Shift::create([
            'cook_id' => $cookId,
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'shift_date' => $request->input('shift_date'),
            'estimated_revenue' => $request->input('estimated_revenue'),
            'shift_status' => 1,
        ]);

        if ($shift) {
            $this->attachMeals($shift, $meals);
            $this->attachPackages($shift, $packages);
            $this->startShiftAction($shift->id);
            $this->computeEstimateShiftRevenue($shift->id);

            $adminControl = ShiftAdminControll::first();
            $message = $shift ? 'Shift created successfully.' : 'Shift created but not started. Standard start time is ' . $adminControl->shift_start_time;

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $this->getShiftDetails($shift->id),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, shift was NOT created. Please try again!',
        ]);
    }

    /**
     * Edit an existing shift.
     */
    public function editShift(string $id)
    {
        $shift = Shift::find($id);

        if ($shift) {
            $shift_meal = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')->where('shift_id', $id)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [$shift, $shift_meal],
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to load shift. Please try again!',
        ]);
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

        $shift->update([
            'end_time' => $request->input('end_time'),
            'shift_date' => $request->input('shift_date'),
            'shift_status' => 1,
        ]);

        $this->attachMeals($shift, $request->input('meals', []));
        $this->attachPackages($shift, $request->input('packages', []));
        $this->startShiftAction($shift->id);
        $this->computeEstimateShiftRevenue($shift->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift updated successfully.',
            'data' => $this->getShiftDetails($shift->id),
        ]);
    }

    /**
     * End an existing shift.
     */
    public function updateShiftstatus($id)
    {
        $shift = Shift::find($id);
        if (!$shift) {
            return response()->json(['error' => 'Shift not found'], 404);
        }

        if ($this->endShiftAction($shift->id)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Shift ended successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, shift was NOT ended. Please try again!',
        ]);
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
}
