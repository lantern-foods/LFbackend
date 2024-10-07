<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CookApprovalStatus;
use App\Models\Cook;
use App\Jobs\cookapproval;
use App\Models\CookDocument;

class CookApprovalController extends Controller
{
    /**
     * Fetch cook details, documents, and approval status
     */
    public function edit(string $id)
    {
        $cook = Cook::find($id);

        if ($cook) {
            $cook_document = CookDocument::where('cook_id', $id)->first();
            $approval = CookApprovalStatus::firstOrCreate(
                ['cook_id' => $id],
                [
                    'kitchen_name_approved' => 0,
                    'id_number_approved' => 0,
                    'mpesa_number_approved' => 0,
                    'health_number_approved' => 0,
                    'health_expiry_date_approved' => 0,
                    'shrt_desc_approved' => 0,
                    'id_front_approved' => 0,
                    'id_back_approved' => 0,
                    'health_cert_approved' => 0,
                    'profile_pic_approved' => 0,
                ]
            );

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [
                    'cook' => $cook,
                    'cook_document' => $cook_document,
                    'approval' => $approval,
                ],
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Cook record not found or access not allowed!',
            ];
        }

        return response()->json($data);
    }

    /**
     * Update cook approval status
     */
    public function updateApprovalStatus(Request $request, string $id)
    {
        // Validate the incoming request
        $request->validate([
            'kitchen_name_approved' => 'required|boolean',
            'id_number_approved' => 'required|boolean',
            'mpesa_number_approved' => 'required|boolean',
            'health_number_approved' => 'required|boolean',
            'health_expiry_date_approved' => 'required|boolean',
            'shrt_desc_approved' => 'required|boolean',
            'id_front_approved' => 'required|boolean',
            'id_back_approved' => 'required|boolean',
            'health_cert_approved' => 'required|boolean',
            'profile_pic_approved' => 'required|boolean',
            'rejection_reason' => 'nullable|string',
        ]);

        $cook = Cook::findOrFail($id);
        $approval = CookApprovalStatus::where('cook_id', $id)->firstOrFail();

        // Update individual approval statuses
        $approval->fill([
            'kitchen_name_approved' => $request->input('kitchen_name_approved'),
            'id_number_approved' => $request->input('id_number_approved'),
            'mpesa_number_approved' => $request->input('mpesa_number_approved'),
            'health_number_approved' => $request->input('health_number_approved'),
            'health_expiry_date_approved' => $request->input('health_expiry_date_approved'),
            'shrt_desc_approved' => $request->input('shrt_desc_approved'),
            'id_front_approved' => $request->input('id_front_approved'),
            'id_back_approved' => $request->input('id_back_approved'),
            'health_cert_approved' => $request->input('health_cert_approved'),
            'profile_pic_approved' => $request->input('profile_pic_approved'),
        ]);

        // Check if all items are approved
        $approval->approved = !in_array(0, $approval->only([
            'kitchen_name_approved', 'id_number_approved', 'mpesa_number_approved', 'health_number_approved',
            'health_expiry_date_approved', 'shrt_desc_approved', 'id_front_approved', 'id_back_approved',
            'health_cert_approved', 'profile_pic_approved'
        ]), true);

        // Set rejection reason if not all approved
        if (!$approval->approved) {
            $approval->rejection_reason = $request->input('rejection_reason', null);
            // Dispatch job for cook approval notification
            dispatch(new cookapproval($cook, $approval));
        }

        $approval->save();

        $data = [
            'status' => 'success',
            'message' => 'Cook approval status updated successfully',
        ];

        return response()->json($data);
    }
}
