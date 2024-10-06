<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meal;
use App\Models\MealImage;
class mealapprovalController extends Controller
{
 
 


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $meal_aprroval = Meal::where('id',$id)->first();

        if (!empty($meal_aprroval))
        {
            $meal_images = MealImage::where('meal_id',$id)->first();
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data'=> [$meal_aprroval, $meal_images]
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'User record not found or access not allowed!'
            ];

        }

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'status' => 'required'
        ],
        [
            'status.required' => 'Approval status is required'
        ]);

        $meal = Meal::findOrFail($id);
        $meal->status=$request->input('status');
        $meal->update();


        if (!empty($meal)) {
            
            $data = [
                'status' => 'success',
                'message' => 'Meal approved successfully!',
                
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Unable to update role. Please try again!',
            ];

        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
