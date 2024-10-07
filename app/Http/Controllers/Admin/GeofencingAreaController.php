<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeofencingArea;
use Illuminate\Http\Request;

class GeofencingAreaController extends Controller
{
    /**
     * Store a newly created geofencing area in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1',
        ]);

        // Create the geofencing area
        try {
            $geofencingArea = GeofencingArea::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Geofencing area created successfully!',
                'data' => $geofencingArea,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to create geofencing area. Please try again!',
            ], 500);
        }
    }
}
