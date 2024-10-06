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
        return response()->json($shifts);

        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // if (!auth()->user()->can('create', ShiftAdminControll::class)) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized']);
        // }
        if ($request->start_time == null || $request->end_time == null) {
            return response()->json(['success' => false, 'message' => 'Please fill all the fields']);
        }
        $all_closed = 0;
        if ($request->all_shifts_closed == true) {
            $all_closed = 1;
        }

        $shiftControl = ShiftAdminControll::create([
            'shift_start_time' => $request->start_time,
            'shift_end_time' => $request->end_time,
            'all_shifts_closed' => $all_closed
        ]);
        if ($shiftControl) {
            return response()->json(['success' => true, 'message' => 'Shift Time Created Successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Something went wrong']);
        }

        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shift = ShiftAdminControll::find($id);
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found']);
        }
        $all_closed = 0;
        if ($request->all_shifts_closed == true) {
            $all_closed = 1;
        }

        $shift->shift_start_time = $request->start_time;
        $shift->shift_end_time = $request->end_time;
        $shift->all_shifts_closed = $all_closed;
        $shift->save();
        return response()->json($shift);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
