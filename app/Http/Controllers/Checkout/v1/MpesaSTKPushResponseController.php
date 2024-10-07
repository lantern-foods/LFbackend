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
    public function processResponse(Request $request)
    {
        // Retrieve JSON input data from the MPESA callback
        $data = json_decode(file_get_contents('php://input'), true);
        \Log::info("MPESA Callback Data", ["Data" => $data]);

        // Ensure the callback contains the necessary data
        if (!isset($data['Body']['stkCallback'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid callback data.',
            ], 400);
        }

        $TxnDateTime = Carbon::now();
        $callback = $data['Body']['stkCallback'];
        $CheckoutRequestID = $callback['CheckoutRequestID'] ?? null;
        $ResultCode = $callback['ResultCode'] ?? null;
        $ResultDesc = $callback['ResultDesc'] ?? null;

        // Find the transaction in the database
        $transaction = DB::table('mpesa_transactions')
            ->where('checkout_request_id', $CheckoutRequestID)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found.',
            ], 404);
        }

        // If the transaction failed, update it and exit early
        if ($ResultCode != 0) {
            DB::table('mpesa_transactions')
                ->where('checkout_request_id', $CheckoutRequestID)
                ->update([
                    'result_code' => $ResultCode,
                    'result_desc' => $ResultDesc,
                    'txn_status' => 'Failed',
                    'updated_at' => $TxnDateTime,
                ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed.',
            ]);
        }

        // Extract callback metadata
        try {
            $TxnAmount = $callback['CallbackMetadata']['Item'][0]['Value'] ?? null;
            $MpesaReceiptNo = $callback['CallbackMetadata']['Item'][1]['Value'] ?? null;
            $SenderPhoneNo = $callback['CallbackMetadata']['Item'][3]['Value'] ?? null;
        } catch (\Exception $e) {
            \Log::error("Error parsing CallbackMetadata", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to parse callback metadata.',
            ], 500);
        }

        // Update transaction and order details within a transaction
        DB::beginTransaction();

        try {
            // Update the mpesa_transactions table
            DB::table('mpesa_transactions')
                ->where('checkout_request_id', $CheckoutRequestID)
                ->update([
                    'result_code' => $ResultCode,
                    'result_desc' => $ResultDesc,
                    'txn_amount' => $TxnAmount,
                    'mpesa_receipt_no' => $MpesaReceiptNo,
                    'sender_phone_no' => $SenderPhoneNo,
                    'txn_status' => 'Successful',
                    'updated_at' => $TxnDateTime,
                ]);

            // Update the related order status
            DB::table('orders')
                ->where('id', $transaction->order_id)
                ->update(['status' => 'Successful Payment']);

            DB::commit();

            \Log::info("Transaction and order updated successfully", ['transaction_id' => $transaction->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating transaction or order", ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment. Please try again!',
            ], 500);
        }
    }
}
