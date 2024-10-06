<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\Customeraddress;
use Auth;
use Illuminate\Http\Request;

class ClientaddressController extends Controller
{

    public function all_address()
    {
        $client_id = Auth::id();
        $customer_addresses = Customeraddress::where('client_id', $client_id)->get();

        if (!$customer_addresses->isEmpty()) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $customer_addresses,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }

        return response()->json($data);

    }
    public function add_address(Request $request)
    {
        $client_id = Auth::id();
        $location_name = $request->input('location_name');
        $address_name = $request->input('address_name');

        $customeraddress = Customeraddress::create([
            'client_id' => $client_id,
            'location_name' => $location_name,
            'address_name' => $address_name,
            'location_status' => 0,

        ]);

        if ($customeraddress) {
            $data = [
                'status' => 'success',
                'message' => 'address created successfully',
            ];
        } else {

            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, address was NOT created. Please try again!',
            ];

        }
        return response()->json($data);
    }

    public function edit(string $id)
    {
        $client_id = Auth::id();
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();
        if (!empty($customer_address)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $customer_address,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your profile. Please try again!',
            ];
        }
        return response()->json($data);

    }

    public function update(Request $request, string $id)
    {

        $client_id = Auth::id();
        $location_name = $request->input('location_name');
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if (!empty($customer_address)) {
            $customer_address->location_name = $location_name;

            if ($customer_address->update()) {

                $data = [
                    'status' => 'success',
                    'message' => 'Address updated successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. address was NOT updated. Please try again!',
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your address for update. Please try again!',
            ];
        }

        return response()->json($data);

    }

    public function deleteAddress(string $id)
    {
        $client_id = Auth::id();
        // Attempt to find the address by ID and client_id to ensure the client owns the address
        $customer_address = Customeraddress::where('id', $id)->where('client_id', $client_id)->first();

        if (!empty($customer_address)) {
            // If the address is found, attempt to delete it
            if ($customer_address->delete()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Address deleted successfully',
                ];
            } else {
                // If the delete operation fails for some reason
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Address was NOT deleted. Please try again!',
                ];
            }
        } else {
            // If no address matches the criteria (not found or doesn't belong to client)
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your address for deletion. Please try again!',
            ];
        }

        return response()->json($data);
    }

}
