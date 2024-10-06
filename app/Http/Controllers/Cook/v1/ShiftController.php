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
use App\Traits\Cooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ShiftController extends Controller
{
    use Cooks;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /*
     * ALl shift
     */
    public function index()
    {
        $shifts = Shift::where('cook_id', Auth::id())->get();

        if (!empty($shifts)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $shifts,
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered. Vehicle was NOt update. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function getShift($cookId)
    {
        $cook = Cook::where('id', $cookId)->first();
        if (empty($cook)) {
            return response()->json(['error' => 'Cook not found'], 404);
        }
        $shifts = Shift::where('cook_id', $cookId)->get();
        //    get shift_meals for each shift
        foreach ($shifts as $shift) {
            $shift->meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')
                ->where('shift_id', $shift->id)
                ->get();
        }

        return response()->json($shifts);
    }

    private function getShiftDetails($shiftId)
    {
        $shift = Shift::where('id', $shiftId)->first();
        if (empty($shift)) {
            return response()->json(['error' => 'Shift not found'], 404);
        }
        $shift->meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')
            ->where('shift_id', $shift->id)
            ->get();
        $shift->packages = ShiftPackage::join('packages', 'shift_packages.package_id', '=', 'packages.id')
            ->where('shift_id', $shift->id)
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
     * Store a newly created resource in storage.
     */
    public function store(ShiftRequest $request)
    {
        $request->validated();
        // extract the required inputs
        $cookId = $request->input('cook_id');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $shiftDate = $request->input('shift_date');
        $estimatedRevenue = $request->input('estimated_revenue');
        $meals = $request->input('meals', []);
        $packages = $request->input('packages', []);
        // check if both meals and packages are null
        if (empty($meals) && empty($packages)) {
            return response()->json(['error' => 'At least one meal or package is required.'], 400);
        }
        // check if shift already exists
        $existingShift = Shift::where('cook_id', $cookId)
            ->first();
        // if (!empty($existingShift->status==)) {
        //     return response()->json([
        //         '
        //     error' => 'Shift already exist for this cook ',
        //         'shift' => $existingShift
        //     ], 400);
        // }
        // create shift
        $shift = Shift::create([
            'cook_id' => $cookId,
            'estimated_revenue' => $estimatedRevenue,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'shift_date' => $shiftDate,
            'shift_status' => 1,
        ]);

        // Attach meals to the shift

        if ($shift) {
            $this->attachMeals($shift, $request->input('meals', []));
            $this->attachPackages($shift, $request->input('packages', []));
            $this->startShiftAction($shift->id);
            $this->computeEstimateShiftRevenue($shift->id);

            if ($shift) {
                $data = [
                    'status' => 'success',
                    'message' => 'Shift created successfully. ',
                    'data' => $this->getShiftDetails($shift->id),
                ];
            } else {
                $adminControl = ShiftAdminControll::get()->first();
                $data = [
                    'status' => 'success',
                    'message' => 'Shift created but not started.Standard start time for shift is ' . $adminControl->shift_start_time,
                    'data' => $this->getShiftDetails($shift->id),
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, shift was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * edit shift
     */
    public function editShift(string $id)
    {
        $shift = Shift::where('id', $id)->first();

        if (!empty($shift)) {
            $shift_meal = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')->where('shift_id', $id)->get();

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [$shift, $shift_meal],
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load shift. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified shift and its meals.
     *
     * @param  \App\Http\Requests\ShiftRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ShiftUpddatRequest $request, $id)
    {
        $shift = Shift::findOrFail($id);
        if (empty($shift)) {
            return response()->json(['error' => 'Shift not found'], 404);
        }
        $request->validated();
        $endTime = $request->input('end_time');
        $shiftDate = $request->input('shift_date');

        $shiftupdate = Shift::where('id', $id)->first()->update([
            'end_time' => $endTime,
            'shift_date' => $shiftDate,
            'shift_status' => 1,
        ]);

        // Attach meals to the shift
        $shift = Shift::where('id', $id)->first();

        if ($shiftupdate == 1) {
            $this->attachMeals($shift, $request->input('meals', []));
            $this->attachPackages($shift, $request->input('packages', []));
            $this->startShiftAction($shift->id);
            $this->computeEstimateShiftRevenue($shift->id);
            $data = [
                'status' => 'success',
                'message' => 'Shift created successfully. ',
                'data' =>
                    $this->getShiftDetails($shift->id),
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, shift was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /*
     * update the shift status
     */
    public function updateShiftstatus($id)
    {
        $shift = Shift::findOrFail($id);

        // Update shift details

        // Meals provided in the request
        if (!empty($shift)) {
            $updated = $this->endShiftAction($shift->id);

            if ($updated) {
                $data = [
                    'status' => 'success',
                    'message' => 'Shift ended successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, your shift was NOT ended. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your shift for update. Please try again!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Attach meals to the shift.
     *
     * @param  \App\Models\Shift  $shift
     * @param  array  $meals
     * @return void
     */
    public function updateShiftMeals(Request $request, $shiftId)
    {
        $shift = Shift::findOrFail($shiftId);
        $meals = $request->input('meals', []);

        if (!empty($meals) && $shift->exists()) {
            return $this->attachMeals($shift, $meals);
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, your shift was NOT updated. Please try again!',
            ];
            return response()->json($data);
        }
        // return response()->json($meals);
    }

    public function getShiftMeals($shiftId)
    {
        $shift = Shift::findOrFail($shiftId);
        $shift_meal = Shiftmeal::where('shift_id', $shift->id);
        $meals = Shiftmeal::join('meals', 'shift_meals.meal_id', '=', 'meals.id')->where('shift_id', $shift->id)->get();
        if (!empty($meals)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $meals,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your shift for update. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function checkIfMealExist(Request $request, $shiftId)
    {
        $shiftMeal = Shiftmeal::where('shift_id', $shiftId)->first();

        $meal = $request->input('meal');
        if (empty($meal)) {
            $data = [
                'status' => 'error',
                'message' => 'No Meals Attached',
            ];
            return response()->json($data);
        }

        if (!empty($shiftMeal)) {
            // update quantity
            $update = $shiftMeal->update([
                'quantity' => $meal['quantity']
            ]);

            if ($update) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity updated successfully',
                    'meal_update' => Meal::where('id', $meal['meal_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
            $data = [
                'status' => 'error',
                'message' => 'Unable to update meal quantity',
            ];
        } else {
            // create new meal
            $creat_res = Shiftmeal::create([
                'shift_id' => $shiftId,
                'meal_id' => $meal['meal_id'],
                'quantity' => $meal['quantity'],
            ]);
            if ($creat_res) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity created successfully',
                    'meal_create' => Meal::where('id', $meal['meal_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
        }
    }

    protected function updateShiftMealIfExistOrAdd($meal, $shiftId)
    {
        $shiftMeal = Shiftmeal::where('shift_id', $shiftId)->first();

        if (empty($meal)) {
            $data = [
                'status' => 'error',
                'message' => 'No Meals Attached',
            ];
            return response()->json($data);
        }

        if (!empty($shiftMeal)) {
            // update quantity
            $update = $shiftMeal->update([
                'quantity' => $meal['quantity']
            ]);

            if ($update) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity updated successfully',
                    'meal_update' => Meal::where('id', $meal['meal_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
            $data = [
                'status' => 'error',
                'message' => 'Unable to update meal quantity',
            ];
        } else {
            // create new meal
            $creat_res = Shiftmeal::create([
                'shift_id' => $shiftId,
                'meal_id' => $meal['meal_id'],
                'quantity' => $meal['quantity'],
            ]);
            if ($creat_res) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity created successfully',
                    'meal_create' => Meal::where('id', $meal['meal_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
        }
    }

    protected function updateShiftPackageIfExistOrAdd($package, $shiftId)
    {
        $shiftPackage = ShiftPackage::where('shift_id', $shiftId)->first();

        if (empty($package)) {
            $data = [
                'status' => 'error',
                'message' => 'No Meals Attached',
            ];
            return response()->json($data);
        }

        if (!empty($shiftPackage)) {
            // update quantity
            $update = $shiftPackage->update([
                'quantity' => $package['quantity']
            ]);

            if ($update) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity updated successfully',
                    'meal_update' => Package::where('id', $package['package_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
            $data = [
                'status' => 'error',
                'message' => 'Unable to update meal quantity',
            ];
        } else {
            // create new meal
            $creat_res = ShiftPackage::create([
                'shift_id' => $shiftId,
                'package_id' => $package['package_id'],
                'quantity' => $package['quantity'],
            ]);
            if ($creat_res) {
                $data = [
                    'status' => 'success',
                    'message' => 'Meal Quantity created successfully',
                    'meal_create' => Package::where('id', $package['package_id'])->update(['express_status' => 1])
                ];
                return response()->json($data);
            }
        }
    }

    protected function attachMeals(Shift $shift, $meals)
    {
        foreach ($meals as $meal) {
            $this->updateShiftMealIfExistOrAdd($meal, $shift->id);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Shift updated successfully',
            'shift' => $shift
        ]);
    }

    protected function attachPackages(Shift $shift, $packages)
    {
        foreach ($packages as $package) {
            $this->updateShiftPackageIfExistOrAdd($package, $shift->id);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Shift updated successfully',
            'shift' => $shift
        ]);
    }

    public function getTotalEarningsPerCookPerShiftDate()
    {
        $totalEarnings = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->join('shifts', 'shifts.cook_id', '=', 'cooks.id')
            ->select(
                'shifts.cook_id',
                'shifts.date as shift_date',
                DB::raw('SUM(order_details.subtotal) as total_earnings')
            )
            ->where('cooks.id', Auth::id())
            ->where('shifts.shift_status', 1)
            ->groupBy('shifts.cook_id', 'shifts.date')
            ->get();

        if (!empty($totalEarnings)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $totalEarnings,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }

    protected function getShiftStatus($shiftId)
    {
        $shift_active = false;
        $shift = Shift::where('id', $shiftId)->first();
        // get admin constraints [  'shift_start_time',   'shift_end_time',    'all_shifts_closed', ]
        $adminShiftControls = ShiftAdminControll::get()->first();
        $shift_start_time = $adminShiftControls->shift_start_time;
        $shift_end_time = $adminShiftControls->shift_end_time;
        $all_shifts_closed = $adminShiftControls->all_shifts_closed;
        if ($all_shifts_closed == 1) {
            $shift_active = false;
            $this->endShiftAction($shiftId);
        }
        // time formats are "15:00:00"
        // check if shift is within the allowed time range
        $shift_time = $shift->start_time;
        if ($shift_time >= $shift_start_time && $shift_time <= $shift_end_time) {
            $shift_active = true;
        } else {
            $shift_active = false;
            $this->endShiftAction($shiftId);
        }
        // check if current time is within the allowed time range
        $current_time = Carbon::now()->format('H:i:s');
        if ($current_time > $shift_end_time) {
            $shift_active = false;
            $this->endShiftAction($shiftId);
        }
        return $shift_active;
    }

    public function allShifts()
    {
        $shift_admin_control = ShiftAdminControll::get()->first();

        $all_shifts = Shift::where('shift_status', 0)
            ->join('cooks', 'shifts.cook_id', '=', 'cooks.id')
            ->leftJoin('cooks_documents', 'shifts.cook_id', '=', 'cooks_documents.cook_id')
            ->where('cooks.status', '=', 2)
            ->select('shifts.*', 'shifts.id', 'cooks_documents.profile_pic', 'cooks.mpesa_number', 'cooks.alt_phone_number', 'cooks.kitchen_name', 'cooks.shrt_desc', 'cooks.google_map_pin')
            ->where('start_time', '>=', $shift_admin_control->shift_start_time)
            ->where('end_time', '<=', $shift_admin_control->shift_end_time)
            ->orderBy('shifts.created_at', 'desc')
            ->get()
            ->map(function ($shift) {
                $this->checkIfShiftShouldEnd($shift->id);
                return $shift;
            });

        if (!empty($all_shifts)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $all_shifts,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    public function allShiftsmeals()
    {
        $all_meals = Cook::with([
            'shifts' => function ($query) {
                $query->where('shift_status', 1);
            },
            'meals.meal_images',
            'shifts.shiftsmeals.meal'
        ])
            ->whereHas('shifts', function ($query) {
                $query->where('shift_status', 1);
            })
            ->get();

        // Filter meals and meals_images based on presence in shiftsmeals
        $all_meals->each(function ($cook) {
            $cook->meals = $cook->meals->filter(function ($meal) use ($cook) {
                // Assuming 'shiftsmeals' contains meal IDs to check against
                $shiftMealsIds = $cook->shifts->flatMap(function ($shift) {
                    return $shift->shiftsmeals->pluck('meal_id');
                })->unique();

                return $shiftMealsIds->contains($meal->id);
            })->values();

            $cook->meals->each(function ($meal) {
                $meal->meals_images = $meal->meals_images->filter(function ($image) use ($meal) {
                    // Perform your filtering logic for images if needed
                    return true;  // Placeholder: adjust according to your logic
                })->values();
            });
        });

        if (!$all_meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $all_meals,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }

        return response()->json($data);
    }
}
