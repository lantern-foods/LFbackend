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

    public function edit(string $id)
    {
        $cook = Cook::where('id',$id)->first();

        if (!empty($cook)) {
            $cook_document = CookDocument::where('cook_id', $id)->first();
            $approval = CookApprovalStatus::where('cook_id', $id)->first();
    
            if (empty($approval)) {
                // Approval record not found, create a new one
                $approval = new CookApprovalStatus;
                $approval->cook_id = $id; // Assuming there's a cook_id field to link to the Cook
                $approval->kitchen_name_approved = 0;
                $approval->id_number_approved = 0;
                $approval->mpesa_number_approved = 0;
                $approval->health_number_approved = 0;
                $approval->health_expiry_date_approved = 0;
                $approval->shrt_desc_approved = 0;
                $approval->id_front_approved = 0;
                $approval->id_back_approved = 0;
                $approval->health_cert_approved = 0;
                $approval->profile_pic_approved = 0;
    
                // Save the new approval record
                $approval->save();
            }
    
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => [$cook, $cook_document, $approval]
            ];
    
        } else {
    
            $data = [
                'status' => 'no_data',
                'message' => 'Cook record not found or access not allowed!'
            ];
    
        }
        return response()->json($data);
    }
    public function updateApprovalStatus(Request $request, string $id)
    {
        $cook = Cook::where('id',$id)->first();
        $approval = CookApprovalStatus::where('cook_id',$id)->first();
        // Updating individual approval statuses based on request input
        
        $approval->kitchen_name_approved = filter_var($request->input('kitchen_name_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->id_number_approved = filter_var($request->input('id_number_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->mpesa_number_approved = filter_var($request->input('mpesa_number_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->health_number_approved = filter_var($request->input('health_number_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->health_expiry_date_approved = filter_var($request->input('health_expiry_date_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->shrt_desc_approved = filter_var($request->input('shrt_desc_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->id_front_approved = filter_var($request->input('id_front_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->id_back_approved = filter_var($request->input('id_back_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->health_cert_approved = filter_var($request->input('health_cert_approved'), FILTER_VALIDATE_BOOLEAN);
        $approval->profile_pic_approved = filter_var($request->input('profile_pic_approved'), FILTER_VALIDATE_BOOLEAN);
        
        // Check if all items are approved
        $allApproved = [
            $approval->kitchen_name_approved,
            $approval->id_number_approved,
            $approval->mpesa_number_approved,
            $approval->health_number_approved,
            $approval->health_expiry_date_approved,
            $approval->shrt_desc_approved,
            $approval->id_front_approved,
            $approval->id_back_approved,
            $approval->health_cert_approved,
            $approval->profile_pic_approved,
        ];

        $approval->approved = !in_array(0, $allApproved, true);

        // Set rejection reason if provided and not all approved
        if (!$approval->approved) {
            $approval->rejection_reason = $request->input('rejection_reason', null);
            dispatch(new cookapproval($cook,$approval));
        }

        $approval->update();


        if ($approval) {
            
            $data = [
                'status' => 'success',
                'message' => 'Cook approval status updated successful'
            ];
        }else {
            
            $data = [
                'status' => 'error',
                'message' => 'An error occurred.Cook approval status was NOT update!'
            ];
        }

        return response()->json($data);
    }


    
}
