<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Jobs\SendClientOtp;
use App\Models\Client;
use App\Traits\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    use Clients;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::all();

        if (!$clients->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $clients,
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
    public function store(Request $request)
    {
        $full_name = $request->input('full_name');
        $phone_number = $request->input('phone_number');
        $email_address = $request->input('email_address');

        list($msisdn, $network) = $this->get_msisdn_network($phone_number);

        if (!$msisdn) {
            $data = [
                'status' => 'error',
                'message' => 'Please enter a valid phone number!',
            ];
        }

        if (!$full_name) {
            $data = [
                'status' => 'error',
                'message' => 'Please enter your full name!',
            ];

            return response()->json($data);
        }
        if ($this->phonenoExists($msisdn)) {
            $data = [
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ];

            return response()->json($data);
        }
        if ($this->emailAddressExists($email_address)) {
            $data = [
                'status' => 'error',
                'message' => 'Email address is already in use by another account!',
            ];

            return response()->json($data);
        }

        $client = Client::create([
            'full_name' => $full_name,
            'phone_number' => $msisdn,
            'email_address' => $email_address,
        ]);
        \Log::info('Client created successfully: ' . $client->id . ' - ' . $full_name . ' - ' . $phone_number);

        if ($client) {
            // Dispatch the SendOtpJob to send OTP asynchronously
            $this->sendOtp($client);

            $data = [
                'status' => 'success',
                'message' => 'Account created successfully. An OTP has be sent to your phone number.',
                'client_id' => $client->email_address,
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, account was NOT created. Please try again!',
            ];
        }

        return response()->json($data);
    }

    public function sendOtp(Client $client)
    {
        // Generate a random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Save the OTP to the client record in the database
        $client->update(['client_otp' => $otp]);

        dispatch(new SendClientOtp($client));

        $data = [
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ];
        return response()->json($data);
    }

    /**
     * Fetch resource for editing.
     */
    public function edit(string $id)
    {
        $client = Client::with('cook')->where('id', $id)->first();

        if (!empty($client)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $client,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your profile. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, string $id)
    {
        $request->validated();

        $fullname = $request->input('full_name');
        $email_address = $request->input('email_address');
        $physical_address = $request->input('email_address');
        $phone_number = $request->input('phone_number');
        $whatsapp_number = $request->input('whatsapp_number');

        if ($this->emailAddressExists($email_address) && !$this->emailBelongsToClient($id, $email_address)) {
            $data = [
                'status' => 'error',
                'message' => 'Email address is already in use by another account!',
            ];
            return response()->json($data);
        } elseif ($this->phonenoExists($phone_number) && !$this->phoneBelongsToClient($id, $phone_number)) {
            $data = [
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ];
            return response()->json($data);
        }

        $client = Client::where('id', $id)->first();

        if (!empty($client)) {
            $client->full_name = $fullname;
            $client->email_address = $email_address;
            $client->phone_number = $phone_number;
            $client->whatsapp_number = $whatsapp_number;
            $client->physical_address = $physical_address;

            if ($client->update()) {
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
