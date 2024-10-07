<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftAdminControll;
use Illuminate\Http\Request;

class ShiftAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = ShiftAdminControll::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $shifts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request input
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'all_shifts_closed' => 'required|boolean',
        ], [
            'start_time.required' => 'Start time is required.',
            'end_time.required' => 'End time is required and must be after start time.',
            'all_shifts_closed.required' => 'Shift closure status is required.',
        ]);

        try {
            $shiftControl = ShiftAdminControll::create([
                'shift_start_time' => $request->start_time,
                'shift_end_time' => $request->end_time,
                'all_shifts_closed' => $request->all_shifts_closed ? 1 : 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Shift time created successfully!',
                'data' => $shiftControl,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create shift. Please try again.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request input
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'all_shifts_closed' => 'required|boolean',
        ]);

        try {
            $shift = ShiftAdminControll::findOrFail($id);

            $shift->update([
                'shift_start_time' => $request->start_time,
                'shift_end_time' => $request->end_time,
                'all_shifts_closed' => $request->all_shifts_closed ? 1 : 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Shift updated successfully!',
                'data' => $shift,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update shift. Please try again.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $shift = ShiftAdminControll::findOrFail($id);
            $shift->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Shift deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete shift. Please try again.',
            ], 500);
        }
    }
}
