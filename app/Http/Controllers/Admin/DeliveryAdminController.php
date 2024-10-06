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

        if (!$deliverycompanies->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $deliverycompanies,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DeliverycompanyRequest $request)
    {
        $request->validated();

        $full_name = $request->input('full_name');
        $phone_number = $request->input('phone_number');
        $email = $request->input('email');
        $company = $request->input('company');
        $location_charge = $request->input('location_charge');

        if ($this->emailAddressExists($email)) {
            $data = [
                'status' => 'error',
                'message' => 'Email address is already in use by another account!',
            ];

            return response()->json($data);
        } elseif ($this->phonenoExists($phone_number)) {
            $data = [
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ];

            return response()->json($data);
        }

        $deliverycompany = Deliverycompany::create([
            'full_name' => $full_name,
            'phone_number' => $phone_number,
            'email' => $email,
            'company' => $company,
            'location_charge' => $location_charge,
        ]);
        if ($deliverycompany) {
            // Dispatch the SendOtpJob to send OTP asynchronously
            $this->sendOtp($deliverycompany);

            $data = [
                'status' => 'success',
                'message' => 'Account created successfully. An OTP has be sent to the delivery company phone number.',
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, account was NOT created. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function sendOtp(Deliverycompany $deliverycompany)
    {
        // Generate a random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Save the OTP to the client record in the database
        $deliverycompany->update(['delvry_otp' => $otp]);

        // dispatch(new SendDeliverycompanyOtp($deliverycompany));

        $data = [
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ];
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $deliverycompany = Deliverycompany::where('id', $id)->first();

        if (!empty($deliverycompany)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $deliverycompany,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'deliverycompany record not found or access not allowed!',
            ];
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeliverycompanyRequest $request, string $id)
    {
        $request->validated();
        $full_name = $request->input('full_name');
        $phone_number = $request->input('phone_number');
        $email = $request->input('email');
        $company = $request->input('company');
        $location_charge = $request->input('location_charge');

        if ($this->emailAddressExists($email) && !$this->emailBelongsToDeliverycompany($id, $email)) {
            $data = [
                'status' => 'error',
                'message' => 'Email is already in use by another delivery company!'
            ];

            return response()->json($data);
        } elseif ($this->phonenoExists($phone_number) && !$this->phoneBelongsToDeliverycompany($id, $phone_number)) {
            $data = [
                'status' => 'error',
                'message' => 'Phone  number is already in use by another user!'
            ];

            return response()->json($data);
        }

        $deliverycompany = Deliverycompany::where('id', $id)->first();

        if (!empty($deliverycompany)) {
            $deliverycompany->full_name = $full_name;
            $deliverycompany->email = $email;
            $deliverycompany->phone_number = $phone_number;
            $deliverycompany->company = $company;
            $deliverycompany->location_charge = $location_charge;

            if ($deliverycompany->update()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Profile was NOT updated. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your profile for update. Please try again!',
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
