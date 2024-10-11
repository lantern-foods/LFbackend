<?php

namespace App\Traits;

use App\Models\Order;
use DB;
use Carbon\Carbon;

trait MpesaUtil
{
    private const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';
    private const PRODUCTION_URL = 'https://api.safaricom.co.ke';

    private const ACCESS_TOKEN_ENDPOINT = '/oauth/v1/generate?grant_type=client_credentials';
    private const STK_PUSH_ENDPOINT = '/mpesa/stkpush/v1/processrequest';

    /**
     * Get the base URL for Mpesa API based on environment
     */
    private function getMpesaBaseUrl(): string
    {
        return config('payment_methods.mpesa.mpesa_env') === 'sandbox' ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    /**
     * Generate Mpesa Access Token
     */
    public function getAccessToken(string $consumerKey, string $consumerSecret)
    {
        $apiUrl = $this->getMpesaBaseUrl() . self::ACCESS_TOKEN_ENDPOINT;
        \Log::info("Access Token URL: {$apiUrl}");

        $credentials = base64_encode("{$consumerKey}:{$consumerSecret}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ]);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            \Log::error("cURL Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            \Log::error("HTTP Error: {$httpCode} Response: {$response}");
            return null;
        }

        $decodedResponse = json_decode($response, true);
        return $decodedResponse['access_token'] ?? null;
    }

    /**
     * Initiate Mpesa STK Push Payment
     */
    public function stkPush(string $phone_no, string $order_id, float $order_total)
    {
        $amount = intval($order_total);
        $timestamp = date('YmdHis');
        $businessShortCode = config('payment_methods.mpesa.business_short_code');
        $passKey = config('payment_methods.mpesa.pass_key');

        $password = base64_encode("{$businessShortCode}{$passKey}{$timestamp}");

        $accessToken = $this->getAccessToken(
            config('payment_methods.mpesa.consumer_key'),
            config('payment_methods.mpesa.consumer_secret')
        );

        if (empty($accessToken)) {
            \Log::error("Failed to generate Mpesa access token.");
            return false;
        }

        $url = $this->getMpesaBaseUrl() . self::STK_PUSH_ENDPOINT;
        $payload = [
            'BusinessShortCode' => $businessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => config('payment_methods.mpesa.transaction_type'),
            'Amount' => $amount,
            'PartyA' => $phone_no,
            'PartyB' => config('payment_methods.mpesa.party_b'),
            'PhoneNumber' => $phone_no,
            'CallBackURL' => config('payment_methods.mpesa.call_back_url'),
            'AccountReference' => 'Order_' . $order_id,
            'TransactionDesc' => 'Order Payment'
        ];

        \Log::info("STK Push Payload: " . json_encode($payload));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Authorization:Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            \Log::error("cURL Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        $responseData = json_decode($response, true);
        curl_close($ch);

        \Log::info("STK Push Response: " . print_r($responseData, true));

        if (!empty($responseData['MerchantRequestID']) && !empty($responseData['CheckoutRequestID'])) {
            return $this->logStkPushResponse($responseData, $order_id);
        } else {
            \Log::error("STK Push Failed: " . $response);
            return false;
        }
    }

    /**
     * Log STK Push response to the database
     */
    private function logStkPushResponse(array $data, string $order_id): bool
    {
        $order = Order::where('order_no', $order_id)->first();

        if ($order) {
            $logData = [
                'order_id' => $order->id,
                'request_date_time' => Carbon::now(),
                'merchant_request_id' => $data['MerchantRequestID'],
                'checkout_request_id' => $data['CheckoutRequestID'],
                'response_code' => $data['ResponseCode'] ?? 'N/A',
                'response_desc' => $data['ResponseDescription'] ?? 'N/A',
                'customer_msg' => $data['CustomerMessage'] ?? 'N/A',
                'created_at' => Carbon::now()
            ];

            DB::table('mpesa_transactions')->insert($logData);
            \Log::info("STK Push response logged successfully!");
            return true;
        } else {
            \Log::error("Order not found for Order ID: {$order_id}");
            return false;
        }
    }
}
