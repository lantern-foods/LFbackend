<?php

namespace App\Http\Controllers\Checkout\v1;

use App\Http\Controllers\Controller;
use App\Traits\Checkout;
use App\Traits\GlobalFunctions;
use App\Traits\MpesaUtil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use GlobalFunctions, MpesaUtil, Checkout;

    /**
     * Initialize MPESA payment
     */
    public function initiateMpesaPayment(Request $request)
    {
        $order_id = $request->input('order_no');
        $phone_no = $this->formatPhoneNumber($request->input('phone_no'));

        if ($phone_no != 'Invalid') {

            $order_total = $this->getOrderTotal($order_id);


            if ($this->stkPush($phone_no, $order_id, $order_total)) {

                $data = [
                    'status' => 'success',
                    'message' => 'MPESA STK Push initiated successfully!',
                ];
            } else {

                $data = [
                    'status' => 'error',
                    'message' => 'MPESA STK Push failed please try again!',
                ];
            }
        } else {

            $data = [
                'status' => 'error',
                'message' => 'Invalid phone number, please check and try again!',
            ];
        }

        return response()->json($data);
    }

    public function all_payments()
    {
        $client_id = Auth::id();

        $payments = DB::table('mpesa_transactions')
            ->join('orders', 'mpesa_transactions.order_id', '=', 'orders.id')
            ->join('order_details', 'order_details.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->where('orders.client_id', $client_id)
            ->get();
        if (!empty($payments)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $payments,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your payments. Please try again!',
            ];
        }
        return response()->json($data);
    }

    public function financialAnalytics(Request $request)
    {
        $cook_id = Auth::id(); // Assuming cook is authenticated and cook's id is the same as Auth id

        // Fetch transactions related to the cook's meals
        $transactions = DB::table('mpesa_transactions')
            ->join('orders', 'mpesa_transactions.order_id', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('meals', 'order_details.meal_id', '=', 'meals.id')
            ->join('cooks', 'meals.cook_id', '=', 'cooks.id')
            ->where('cooks.id', $cook_id)
            ->select(
                'mpesa_transactions.id',
                'mpesa_transactions.amount',
                'mpesa_transactions.transaction_date',
                'meals.name as meal_name',
                'orders.id as order_id',
                'orders.created_at as order_date'
            )
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No transactions found for this cook.'
            ]);
        }

        // Process transactions to generate analytics
        $totalEarnings = $transactions->sum('amount');
        $mostPopularMeal = $transactions->groupBy('meal_name')
            ->sortDesc()
            ->keys()
            ->first();
        $totalOrders = $transactions->count('order_id');

        // Prepare analytics data
        $data = [
            'status' => 'success',
            'message' => 'Financial analytics generated successfully.',
            'data' => [
                'total_earnings' => $totalEarnings,
                'most_popular_meal' => $mostPopularMeal,
                'total_orders' => $totalOrders,
                'detailed_transactions' => $transactions
            ]
        ];

        return response()->json($data);
    }
}
