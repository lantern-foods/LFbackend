<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Markup;
use Illuminate\Http\Request;

class markupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $markups = Markup::all();

        if (!$markups->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $markups,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'order_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'mark_up' => 'required',
        ],
            [
                'order_type.required' => 'Order Type is required!',
                'start_date.required' => 'Start date is required!',
                'end_date.required' => 'End date is required!',
                'mark_up.required' => 'Mark up Type is required!',
            ]);

        $order_type = $request->input('order_type');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $mark_up = $request->input('mark_up');

        $markup = Markup::create([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'mark_up' => $mark_up,
            'order_type' => $order_type,
            'status' => 1,
        ]);

        if ($markup) {
            $data = [
                'status' => 'success',
                'message' => 'Markup created successfully!',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Unable to create markup. Please try again!',
            ];
        }

        return response()->json($data);
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
