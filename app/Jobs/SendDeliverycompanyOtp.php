<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Deliverycompany;
use AfricasTalking\SDK\AfricasTalking;
use App\Traits\GlobalFunctions;
use Illuminate\Support\Facades\Log;

class SendDeliverycompanyOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $company_name;
    private $account_verification_code;
    private $phone_no;

    /**
     * Create a new job instance.
     *
     * @param  Deliverycompany  $deliverycompany
     */
    public function __construct(Deliverycompany $deliverycompany)
    {
        $this->company_name = $deliverycompany->company;
        $this->account_verification_code = $deliverycompany->delvry_otp;
        $this->phone_no = $deliverycompany->phone_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = $this->buildMessage();
        $recipient = $this->formatPhoneNumber($this->phone_no);

        if ($this->isValidPhoneNumber($recipient)) {
            $this->sendOtp($recipient, $message);
        } else {
            Log::error('Sending activation code: Invalid phone number ' . $this->phone_no);
        }
    }

    /**
     * Build the OTP message to be sent.
     *
     * @return string
     */
    private function buildMessage(): string
    {
        return 'Dear ' . $this->company_name . ', your Lantern Foods activation code is: ' . $this->account_verification_code;
    }

    /**
     * Validate the phone number format.
     *
     * @param string $phone_no
     * @return bool
     */
    private function isValidPhoneNumber(string $phone_no): bool
    {
        return strlen($phone_no) === 12; // Assuming valid phone number length is 12 digits
    }

    /**
     * Send the OTP using Africa's Talking API.
     *
     * @param string $recipient
     * @param string $message
     * @return void
     */
    private function sendOtp(string $recipient, string $message): void
    {
        try {
            $username = config('sms.sms.at_username');
            $apiKey = config('sms.sms.at_api_key');
            $from = 'Br_Lantern';

            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            $sms->send([
                'to' => $recipient,
                'message' => $message,
                'from' => $from
            ]);

            Log::info('OTP sent successfully to ' . $recipient);
        } catch (\Exception $e) {
            Log::error('SMS Error: Unable to send OTP to ' . $recipient . '. Error: ' . $e->getMessage());
        }
    }
}
