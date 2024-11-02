<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientaddressController extends Controller
{
    /**
     * Fetch all addresses for the authenticated client
     */
    public function all_address()
    {
        $client_id = Auth::id();
        $customer_addresses = Customeraddress::where('client_id', $client_id)->get();

        if ($customer_addresses->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $customer_addresses,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'No records found.',
        ], 404);
    }

    /**
     * Add a new address for the authenticated client
     */
    public function add_address(Request $request)
    {
        $request->validate([
            'location_name' => 'required|string',
            'address_name' => 'required|string',
        ]);

        $client_id = Auth::id();
        $customeraddress = Customeraddress::create([
            'client_id' => $client_id,
            'location_name' => $request->input('location_name'),
            'address_name' => $request->input('address_name'),
            'location_status' => 0,
        ]);

        return $customeraddress ? response()->json([
            'status' => 'success',
            'message' => 'Address created successfully.',
        ]) : response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered, address was not created. Please try again!',
        ], 500);
    }

    /**
     * Edit an address by ID for the authenticated client
     */
    public function edit(string $id)
    {
        $client_id = Auth::id();
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if ($customer_address) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $customer_address,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to load the address. Please try again!',
        ], 404);
    }

    /**
     * Update an address by ID for the authenticated client
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'location_name' => 'required|string',
        ]);

        $client_id = Auth::id();
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if ($customer_address) {
            $customer_address->location_name = $request->input('location_name');

            return $customer_address->save() ? response()->json([
                'status' => 'success',
                'message' => 'Address updated successfully.',
            ]) : response()->json([
                'status' => 'error',
                'message' => 'A problem was encountered. Address was not updated. Please try again!',
            ], 500);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to locate your address for update. Please try again!',
        ], 404);
    }

    /**
     * Delete an address by ID for the authenticated client
     */
    public function deleteAddress(string $id)
    {
        $client_id = Auth::id();
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if ($customer_address) {
            return $customer_address->delete() ? response()->json([
                'status' => 'success',
                'message' => 'Address deleted successfully.',
            ]) : response()->json([
                'status' => 'error',
                'message' => 'A problem was encountered. Address was not deleted. Please try again!',
            ], 500);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to locate your address for deletion. Please try again!',
        ], 404);
    }
}
