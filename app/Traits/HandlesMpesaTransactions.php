<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

trait HandlesMpesaTransactions
{
    /**
     * URL constants for the Mpesa API
     */
    private const ACCESS_TOKEN_URL = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    private const MPESA_B2C_URL = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'; // Replace with the correct endpoint if needed

    /**
     * Get Mpesa API Access Token
     */
    protected function getAccessToken()
    {
        try {
            $client = new Client();
            $response = $client->request('GET', self::ACCESS_TOKEN_URL, [
                'auth' => [env('MPESA_CONSUMER_KEY'), env('MPESA_CONSUMER_SECRET')],
            ]);

            $body = json_decode($response->getBody(), true);

            return $body['access_token'] ?? null;
        } catch (RequestException $e) {
            \Log::error('Failed to get Mpesa access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send Mpesa Payment via B2C API
     */
    public function sendMpesaPayment($phoneNumber, $amount, $remarks)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['error' => 'Unable to retrieve access token'];
        }

        try {
            $client = new Client();
            $response = $client->post(self::MPESA_B2C_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'InitiatorName' => env('MPESA_INITIATOR_NAME'),
                    'SecurityCredential' => env('MPESA_SECURITY_CREDENTIAL'),
                    'CommandID' => 'BusinessPayment',
                    'Amount' => $amount,
                    'PartyA' => env('MPESA_SHORT_CODE'),
                    'PartyB' => $this->formatPhoneNumber($phoneNumber),
                    'Remarks' => $remarks,
                    'QueueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL'),
                    'ResultURL' => env('MPESA_RESULT_URL'),
                    'Occasion' => 'Payment',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            \Log::error('Mpesa B2C payment failed: ' . $e->getMessage());
            return ['error' => 'Mpesa payment failed'];
        }
    }

    /**
     * Format phone number to ensure it's in the correct format for Mpesa
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Format phone number to start with '254' if it starts with '07' or '01'
        if (preg_match('/^(07|01)\d{8}$/', $phoneNumber)) {
            return '254' . substr($phoneNumber, 1);
        }
        return $phoneNumber;
    }
}
