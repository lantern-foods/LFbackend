<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class MpesaSTKPushResponseController extends Controller
{
    /**
     * Process MPESA STK Response
     */
    public function processResponse()
    {
        $data  = json_decode(file_get_contents('php://input'), true);

        \Log::info("Callback Data", ["Data" => $data]);

        $TxnDateTime = Carbon::now();
        $CheckoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'];
        $ResultCode = $data['Body']['stkCallback']['ResultCode'];
        $ResultDesc = $data['Body']['stkCallback']['ResultDesc'];
        $transaction = DB::table('mpesa_transactions')
            ->where('checkout_request_id', $CheckoutRequestID)
            ->first();
        if ($ResultCode != 0) {
            $transaction->update([
                'result_code' => $ResultCode,
                'result_desc' => $ResultDesc,
                'txn_status' => 'Failed',

                'updated_at' => $TxnDateTime
            ]);
        }

        $TxnAmount = $data['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'];
        $MpesaReceiptNo = $data['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        $SenderPhoneNo = $data['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];



        if ($transaction) {
            $update = DB::table('mpesa_transactions')
                ->where('checkout_request_id', $CheckoutRequestID)
                ->update([
                    'result_code' => $ResultCode,
                    'result_desc' => $ResultDesc,
                    'txn_amount' => $TxnAmount,
                    'mpesa_receipt_no' => $MpesaReceiptNo,
                    'sender_phone_no' => $SenderPhoneNo,
                    'updated_at' => $TxnDateTime
                ]);

            if ($update) {
                // Use $transaction->order_id to update the order status
                $order = DB::table('orders')->where('id', $transaction->order_id)->update(['status' => 'Successful Payment']);
                $data = [
                    'status' => 'success',
                    'message' => 'Payment successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, your order was NOT updated. Please try again!'
                ];
                \Log::info("Unable to log response!");
            }
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Transaction not found.'
            ];
        }

        return response()->json($data);
    }
}
