<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeofencingArea;
use Illuminate\Http\Request;

class GeofencingAreaController extends Controller
{
    public function store(Request $request)
    {
        $dt = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:1',
        ]);

        $geofencingarea = GeofencingArea::create($dt);

        if ($geofencingarea) {
            $data = [
                'status' => 'success',
                'message' => 'Geofencing area created successfully!!',
            ];

        } else {

            $data = [
                'status' => 'error',
                'message' => 'Unable to create markup. Please try again!',
            ];

        }

        return response()->json($data);

    }
}
