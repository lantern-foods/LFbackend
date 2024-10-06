<?php

namespace App\Traits;

use GuzzleHttp\Client;

trait HandlesMpesaTransactions
{
    protected function getAccessToken()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials', [
            'auth' => [env('MPESA_CONSUMER_KEY'), env('MPESA_CONSUMER_SECRET')],
        ]);

        $body = json_decode($response->getBody());

        return $body->access_token;
    }

    public function sendMpesaPayment($phoneNumber, $amount, $remarks)
    {
        $client = new Client();
        $accessToken = $this->getAccessToken();

        $response = $client->post(env('MPESA_B2C_URL'), [
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
                'PartyB' => $phoneNumber,
                'Remarks' => $remarks,
                'QueueTimeOutURL' => env('MPESA_CALLBACK_URL'),
                'ResultURL' => env('MPESA_CALLBACK_URL'),
                'Occasion' => 'Payment',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
