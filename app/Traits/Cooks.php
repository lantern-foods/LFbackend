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
        $shift = Shift::find($shift_id);
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

        $shift = Shift::find($shift_id);
        if ($adminShiftControls && ($adminShiftControls->all_shifts_closed == 1 ||
            $current_time >= $adminShiftControls->shift_end_time ||
            ($shift && $current_time >= $shift->end_time))) {
            return $this->endShiftAction($shift_id);
        }

        return false;
    }

    /**
     * Start the specified shift if it has meals or packages
     */
    public function startShiftAction($shift_id)
    {
        $shift = Shift::find($shift_id);
        if (!$shift) {
            return false;
        }

        $total_items = $this->getTotalShiftItems($shift_id);
        $adminShiftControls = ShiftAdminControll::first();
        $shift_start_time = $adminShiftControls->shift_start_time ?? '00:00:00';

        if ($total_items > 0 || $shift->start_time >= $shift_start_time) {
            $shift->update(['shift_status' => 1]);
            $this->updateMealAndPackageStatuses($shift->id, 1, 0); // express on, booked off
            return true;
        }
        return false;
    }

    /**
     * Update meal and package statuses based on shift status
     */
    private function updateMealAndPackageStatuses($shift_id, $express_status, $booked_status)
    {
        // Batch update meals for the shift
        Meal::whereIn('id', Shiftmeal::where('shift_id', $shift_id)->pluck('meal_id'))
            ->update(['express_status' => $express_status, 'booked_status' => $booked_status]);

        // Batch update packages for the shift
        Package::whereIn('id', ShiftPackage::where('shift_id', $shift_id)->pluck('package_id'))
            ->update(['express_status' => $express_status, 'booked_status' => $booked_status]);
    }

    /**
     * Compute and update the estimated revenue for the specified shift
     */
    public function computeEstimateShiftRevenue($shift_id)
    {
        // Sum the total revenue for meals and packages
        $meal_total = Shiftmeal::with('meal')->where('shift_id', $shift_id)->get()->sum(function ($shift_meal) {
            return $shift_meal->meal ? $shift_meal->meal->meal_price * $shift_meal->quantity : 0;
        });

        $package_total = ShiftPackage::with('package')->where('shift_id', $shift_id)->get()->sum(function ($shift_package) {
            return $shift_package->package ? $shift_package->total_price * $shift_package->quantity : 0;
        });

        // Update the shift's estimated revenue
        Shift::where('id', $shift_id)->update(['estimated_revenue' => $meal_total + $package_total]);
    }

    /**
     * Get total shift items
     */
    private function getTotalShiftItems($shift_id)
    {
        return Shiftmeal::where('shift_id', $shift_id)->sum('quantity') +
            ShiftPackage::where('shift_id', $shift_id)->sum('quantity');
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
