<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Markup;
use Illuminate\Http\Request;

class MarkupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $markups = Markup::all();

        return !$markups->isEmpty() ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $markups,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'order_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'mark_up' => 'required|numeric',
        ], [
            'order_type.required' => 'Order Type is required!',
            'start_date.required' => 'Start date is required!',
            'end_date.required' => 'End date is required!',
            'end_date.after_or_equal' => 'End date must be after or equal to start date!',
            'mark_up.required' => 'Mark up is required!',
        ]);

        // Create a new markup record
        try {
            $markup = Markup::create([
                'order_type' => $request->input('order_type'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'mark_up' => $request->input('mark_up'),
                'status' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Markup created successfully!',
                'data' => $markup,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to create markup. Please try again!',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $markup = Markup::find($id);

        return $markup ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $markup,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'Markup not found!',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $markup = Markup::find($id);

        if ($markup) {
            $request->validate([
                'order_type' => 'sometimes|required|string',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'mark_up' => 'sometimes|required|numeric',
            ]);

            try {
                $markup->update($request->only(['order_type', 'start_date', 'end_date', 'mark_up']));

                return response()->json([
                    'status' => 'success',
                    'message' => 'Markup updated successfully!',
                    'data' => $markup,
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to update markup. Please try again!',
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Markup not found!',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $markup = Markup::find($id);

        if ($markup) {
            try {
                $markup->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Markup deleted successfully!',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to delete markup. Please try again!',
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Markup not found!',
            ], 404);
        }
    }
}
