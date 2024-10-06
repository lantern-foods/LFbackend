<?php

namespace App\Traits;

use App\Models\Cook;
use App\Models\Meal;
use App\Models\Package;
use App\Models\Shift;
use App\Models\ShiftAdminControll;
use App\Models\Shiftmeal;
use App\Models\ShiftPackage;
use Carbon\Carbon;

trait Cooks
{
    /**
     * Check whether kitchen name exists
     */
    public function kitchenNameExists($kitchen_name)
    {
        return Cook::where('kitchen_name', $kitchen_name)->exists();
    }

    /**
     * Check whether Mpesa number exists
     */
    public function mpesanoExists($mpesa_number)
    {
        return Cook::where('mpesa_number', $mpesa_number)->exists();
    }

    /**
     * Check if kitchen name belongs to the specified cook
     */
    public function kitchennameBelongsToCook($cook_id, $kitchen_name)
    {
        return Cook::where('id', $cook_id)->where('kitchen_name', $kitchen_name)->exists();
    }

    /**
     * Check if Mpesa number belongs to the specified cook
     */
    public function mpesanoBelongsToCook($cook_id, $mpesa_number)
    {
        return Cook::where('id', $cook_id)->where('mpesa_number', $mpesa_number)->exists();
    }

    /**
     * End the specified shift and reset meal/package statuses
     */
    public function endShiftAction($shift_id)
    {
        $shift = Shift::findOrFail($shift_id);
        if ($shift) {
            // Reset meals and packages for the shift
            $this->updateMealAndPackageStatuses($shift->id, 0, 1); // express off, booked on
            $shift->update(['shift_status' => 0]);
            return true;
        }
        return false;
    }

    /**
     * Check if the shift should be ended based on meals, packages, and admin controls
     */
    public function checkIfShiftShouldEnd($shift_id)
    {
        // Sum the total items in the shift
        $total_items = Shiftmeal::where('shift_id', $shift_id)->sum('quantity') +
                       ShiftPackage::where('shift_id', $shift_id)->sum('quantity');

        // If no items are left, end the shift
        if ($total_items == 0) {
            return $this->endShiftAction($shift_id);
        }

        // Check admin controls for shift end
        $adminShiftControls = ShiftAdminControll::first();
        $current_time = Carbon::now()->format('H:i:s');

        if ($adminShiftControls->all_shifts_closed == 1 ||
            $current_time >= $adminShiftControls->shift_end_time ||
            $current_time >= Shift::find($shift_id)->end_time) {
            return $this->endShiftAction($shift_id);
        }

        return false;
    }

    /**
     * Start the specified shift if it has meals or packages
     */
    public function startShiftAction($shift_id)
    {
        $shift = Shift::findOrFail($shift_id);
        $shift_started = false;
        if ($shift) {
            // Sum the total items in the shift
            $total_items = Shiftmeal::where('shift_id', $shift->id)->sum('quantity') +
                           ShiftPackage::where('shift_id', $shift->id)->sum('quantity');

            // Get admin controls
            $adminShiftControls = ShiftAdminControll::first();
            $shift_start_time = $adminShiftControls->shift_start_time;

            // Start the shift if there are items or the time meets the start condition
            if ($total_items > 0 || $shift->start_time >= $shift_start_time) {
                $shift->update(['shift_status' => 1]);
                $shift_started = true;

                // Update meal and package statuses
                $this->updateMealAndPackageStatuses($shift->id, 1, 0); // express on, booked off
            }
        }
        return $shift_started;
    }

    /**
     * Update meal and package statuses based on shift status
     */
    private function updateMealAndPackageStatuses($shift_id, $express_status, $booked_status)
    {
        // Update meals for the shift
        Shiftmeal::where('shift_id', $shift_id)->each(function ($shift_meal) use ($express_status, $booked_status) {
            Meal::where('id', $shift_meal->meal_id)
                ->update(['express_status' => $express_status, 'booked_status' => $booked_status]);
        });

        // Update packages for the shift
        ShiftPackage::where('shift_id', $shift_id)->each(function ($shift_package) use ($express_status, $booked_status) {
            Package::where('id', $shift_package->package_id)
                ->update(['express_status' => $express_status, 'booked_status' => $booked_status]);
        });
    }

    /**
     * Compute and update the estimated revenue for the specified shift
     */
    public function computeEstimateShiftRevenue($shift_id)
    {
        // Sum the total revenue for meals
        $meal_total = Shiftmeal::with('meal')->where('shift_id', $shift_id)->get()->sum(function ($shift_meal) {
            return $shift_meal->meal ? $shift_meal->meal->meal_price * $shift_meal->quantity : 0;
        });

        // Sum the total revenue for packages
        $package_total = ShiftPackage::with('package')->where('shift_id', $shift_id)->get()->sum(function ($shift_package) {
            return $shift_package->package ? $shift_package->total_price * $shift_package->quantity : 0;
        });

        // Compute total revenue
        $total_revenue = $meal_total + $package_total;

        // Update the shift's estimated revenue
        Shift::where('id', $shift_id)->update(['estimated_revenue' => $total_revenue]);
    }

    /**
     * Placeholder for decrementing meals on package creation
     */
    public function decrementMealsOnCreatePackage($package_id)
    {
        $package = Package::find($package_id);
        // Logic to decrement meals based on package contents can be added here
    }
}
