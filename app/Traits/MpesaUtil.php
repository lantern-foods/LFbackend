<?php

namespace App\Traits;

use App\Models\Order;
use DB;
use Carbon\Carbon;

trait MpesaUtil
{

    /*
        * Generate access Token
        */
    public function getAccessToken($consumerKey, $consumerSecret)
    {

        if (config('payment_methods.mpesa.mpesa_env') == 'sandbox') {
            $apiUrl = 'https://sandbox.safaricom.co.ke';
        } else {
            $apiUrl = 'https://api.safaricom.co.ke';
        }

        \Log::info("Token URL " . $apiUrl);

        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '' . $apiUrl . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials, 'Content-Type: application/json'));

        // ignore ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        \Log::info("Token Response " . $response);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            \Log::error("cURL Error: " . $error);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            \Log::error("HTTP Error: " . $httpCode . " Response: " . $response);
            return null;
        }

        $response = json_decode($response);
        \Log::info("Token response: " . print_r($response, true));

        if (!empty($response) && isset($response->access_token)) {
            $this->access_token = $response->access_token;
            return $this->access_token;
        } else {
            $this->access_token = null;
            return null;
        }
    }

    public function stkPush($phone_no, $order_id, $order_total)
    {
        $amount_to_pay = $order_total;
        $partyA = $phone_no;
        $businessShortCode = config('payment_methods.mpesa.business_short_code');
        $amount_without_decimal = explode(".", $amount_to_pay);
        $amount = $amount_without_decimal[0];


        \Log::info("Short code " . $businessShortCode);

        $callBackUrl = config('payment_methods.mpesa.call_back_url');

        \Log::info("CallBack URL " . $callBackUrl);

        /*
            * Generate password
            */
        $timestamp = date('YmdHis');
        $passKey = config('payment_methods.mpesa.pass_key');
        $passwd = base64_encode($businessShortCode . $passKey . $timestamp);

        /*
            * Generate access token
            */
        $consumerKey = config('payment_methods.mpesa.consumer_key');
        $consumerSecret = config('payment_methods.mpesa.consumer_secret');
        $partyB = config('payment_methods.mpesa.party_b');
        $transaction_type = config('payment_methods.mpesa.transaction_type');

        $accessToken = $this->getAccessToken($consumerKey, $consumerSecret);

        \Log::info("Token " . $accessToken);
        if (!empty($accessToken)) {

            if (config('payment_methods.mpesa.mpesa_env') == 'sandbox') {
                $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            } else {
                $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $accessToken));


            $curl_post_data = array(
                //Fill in the request parameters with valid values
                'BusinessShortCode' => $businessShortCode,
                'Password' => $passwd,
                'Timestamp' => $timestamp,
                'TransactionType' => $transaction_type,
                'Amount' => $amount,
                'PartyA' => $partyA,
                'PartyB' => $partyB,
                'PhoneNumber' => $partyA,
                'CallBackURL' => $callBackUrl,
                'AccountReference' => "N/A",
                'TransactionDesc' => 'Subscription Payment'
            );

            $data_string = json_encode($curl_post_data);

            \Log::info("Curl Post data " . $data_string);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

            $curl_response = curl_exec($curl);

            $data  = json_decode($curl_response, true);

            \Log::info("Request " . $curl_response);

            if (!empty($data)) {
                if (!empty($data['MerchantRequestID']) && !empty($data['CheckoutRequestID'])) {
                    $MerchantRequestID = $data['MerchantRequestID'];
                    $CheckoutRequestID = $data['CheckoutRequestID'];
                    $ResponseCode = $data['ResponseCode'];
                    $ResponseDescription = $data['ResponseDescription'];
                    $CustomerMessage = $data['CustomerMessage'];
                    $request_date_time = Carbon::now();
                    $order_idd = Order::where('order_no', $order_id)->first()->id;

                    $stk_curl_response = array(
                        "order_id" => $order_idd,
                        "request_date_time" => $request_date_time,
                        "merchant_request_id" => $MerchantRequestID,
                        "checkout_request_id" => $CheckoutRequestID,
                        "response_code" => $ResponseCode,
                        "response_desc" => $ResponseDescription,
                        "customer_msg" => $CustomerMessage,
                        "created_at" => $request_date_time
                    );

                    //Set Checkout Request ID in session for use in verifying payment
                    session()->put('CheckoutRequestID', $CheckoutRequestID);

                    //Save MPESA payment details
                    $log_stk_curl_resp = DB::table('mpesa_transactions')->insert([$stk_curl_response]);
                    if ($log_stk_curl_resp) {
                        \Log::info("STK curl response was saved successfully!");

                        return true;
                    } else {
                        \Log::error("A problem as encountered,STK curl response was NOT saved!");

                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
