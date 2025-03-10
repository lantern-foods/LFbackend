<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Jobs\SendClientOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\Clients;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    use Clients;

    /**
     * Display a listing of the clients.
     */
    public function index()
    {
        $clients = Client::all();

        if ($clients->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful.',
                'data' => $clients,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No clients found.',
        ], 404);
    }

    /**
     * Store a newly created client in storage.
     * CHANGE: INCONSISTENT WITH FRONTEND FORM FIELD NAMES
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email_address' => 'required|string|email|max:255|unique:clients,email_address',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $full_name = $request->input('full_name');
        $phone_number = $this->sanitizePhoneNumber($request->input('phone_number'));
        $email_address = $request->input('email_address');

        // Check for duplicate phone number
        if ($this->phonenoExists($phone_number)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number is already in use by another account!',
            ], 400);
        }

        $client = Client::create([
            'full_name' => $full_name,
            'phone_number' => $phone_number,
            'email_address' => $email_address,
        ]);

        if ($client) {
            // Dispatch job to send OTP asynchronously
            $this->sendOtp($client);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. An OTP has been sent to your phone number.',
                'client_id' => $client->id,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem occurred, account was not created. Please try again!',
        ], 500);
    }

    /**
     * Sanitize phone number to remove special characters and normalize it to '254' format.
     *
     * @param string $phone_number
     * @return string
     */
    private function sanitizePhoneNumber($phone_number)
    {
        // Remove all non-numeric characters
        $cleaned_number = preg_replace('/\D/', '', $phone_number);

        // If it starts with 07 or 01, replace with 254
        if (preg_match('/^0[17]/', $cleaned_number)) {
            $cleaned_number = preg_replace('/^0/', '254', $cleaned_number);
        }

        // Ensure it starts with 254
        if (!preg_match('/^254/', $cleaned_number)) {
            return ''; // Invalid format
        }

        return $cleaned_number;
    }

    /**
     * Send OTP to client.
     */
    public function sendOtp(Client $client)
    {
        // Generate a random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Save the OTP to the client record
        $client->update(['client_otp' => $otp]);

        // Dispatch the job to send the OTP
        dispatch(new SendClientOtp($client));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.',
        ]);
    }

    /**
     * Fetch resource for editing.
     */
    public function edit(string $id)
    {
        $client = Client::with('cook')->find($id);

        if ($client) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful.',
                'data' => $client,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Client not found.',
        ], 404);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email_address' => 'required|string|email|max:255|unique:clients,email_address,' . $id,
            'phone_number' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Client not found.',
            ], 404);
        }

        $client->update([
            'full_name' => $request->input('full_name'),
            'email_address' => $request->input('email_address'),
            'phone_number' => $this->sanitizePhoneNumber($request->input('phone_number')),
            'whatsapp_number' => $request->input('whatsapp_number'),
            'physical_address' => $request->input('physical_address'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Client profile updated successfully.',
        ]);
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::find($id);

        if ($client) {
            $client->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Client deleted successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Client not found.',
        ], 404);
    }
}
