<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliverycompanyRequest;
use App\Http\Requests\UpdateDeliverycompanyRequest;
use App\Jobs\SendDeliverycompanyOtp;
use App\Models\Deliverycompany;
use App\Traits\Deliverycompanies;
use Illuminate\Http\Request;

class DeliveryAdminController extends Controller
{
    use Deliverycompanies;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deliverycompanies = Deliverycompany::all();

        $data = !$deliverycompanies->isEmpty() ? [
            'status' => 'success',
            'message' => 'Request successful',
            'data' => $deliverycompanies,
        ] : [
            'status' => 'no_data',
            'message' => 'No records found',
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DeliverycompanyRequest $request)
    {
        $request->validated();

        if ($this->emailAddressExists($request->input('email'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email address is already in use by another account!',
            ]);
        }

        if ($this->phonenoExists($request->input('phone_number'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ]);
        }

        $deliverycompany = Deliverycompany::create($request->only([
            'full_name', 'phone_number', 'email', 'company', 'location_charge'
        ]));

        if ($deliverycompany) {
            // Dispatch OTP job asynchronously
            $this->sendOtp($deliverycompany);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. An OTP has been sent to the delivery company\'s phone number.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Account creation failed. Please try again!',
        ]);
    }

    /**
     * Send OTP to a delivery company
     */
    public function sendOtp(Deliverycompany $deliverycompany)
    {
        // Generate and save OTP
        $otp = mt_rand(100000, 999999);
        $deliverycompany->update(['delvry_otp' => $otp]);

        // Dispatch the job to send OTP asynchronously
        dispatch(new SendDeliverycompanyOtp($deliverycompany));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ]);
    }

    /**
     * Edit a delivery company profile
     */
    public function edit(string $id)
    {
        $deliverycompany = Deliverycompany::find($id);

        return $deliverycompany ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $deliverycompany,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'Delivery company record not found!',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeliverycompanyRequest $request, string $id)
    {
        $request->validated();

        if ($this->emailAddressExists($request->input('email')) && !$this->emailBelongsToDeliverycompany($id, $request->input('email'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email is already in use by another delivery company!',
            ]);
        }

        if ($this->phonenoExists($request->input('phone_number')) && !$this->phoneBelongsToDeliverycompany($id, $request->input('phone_number'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ]);
        }

        $deliverycompany = Deliverycompany::find($id);

        if ($deliverycompany) {
            $deliverycompany->update($request->only([
                'full_name', 'phone_number', 'email', 'company', 'location_charge'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Delivery company profile not found!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deliverycompany = Deliverycompany::find($id);

        if ($deliverycompany) {
            $deliverycompany->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery company deleted successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to delete delivery company. It may not exist!',
        ]);
    }
}
