<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\Client;
use App\Traits\GlobalFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendClientOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $client_name;
    private $account_verification_code;
    private $phone_no;

    protected $client;

    /**
     * Create a new job instance.
     *
     * @param  Client  $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client_name = $client->full_name;
        $this->account_verification_code = $client->client_otp;
        $this->phone_no = $client->phone_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send account activation code
        $message = 'Dear ' . $this->client_name . ', your Lantern Foods activation code is: ' . $this->account_verification_code . 'Do not share your code. Read the
        terms and conditions.';

        // $recipient = $this->formatPhoneNumber($this->phone_no);
        $recipient = $this->phone_no;

        if ($recipient != 'Invalid') {
            if (strlen($recipient) == 12) {
                $username = config('sms.sms.at_username');
                $apiKey = config('sms.sms.at_api_key');

                // Set your shortCode or senderId
                $from = 'Br_Lantern';

                // Initialize the SDK
                $AT = new AfricasTalking($username, $apiKey);

                // Get the SMS service
                try {
                    $sms = $AT->sms();

                    // Use the service
                    $result = $sms->send([
                        'to' => '+' . $recipient,
                        'message' => $message,
                        'from' => $from
                    ]);
                } catch (Exception $e) {
                    \Log::error('SMS Error:  ' . $phone_no . $e->getMessage());
                }
            } else {
                \Log::error('Sending activation code: Invalid phone number ' . $this->phone_no);
            }
        } else {
            \Log::error('Sending activation code: Invalid phone number ' . $this->phone_no);
        }
    }
}
