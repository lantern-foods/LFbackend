<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\Driver;
use App\Traits\GlobalFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDriverOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $driver_name;
    private $account_verification_code;
    private $phone_no;

    /**
     * Create a new job instance.
     *
     * @param  Driver  $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver_name = $driver->driver_name;
        $this->account_verification_code = $driver->drive_otp;
        $this->phone_no = $driver->phone_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Construct the OTP message
        $message = $this->buildMessage();
        $recipient = $this->formatPhoneNumber($this->phone_no);

        // Validate the recipient phone number before sending OTP
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
        return 'Dear ' . $this->driver_name . ', your Lantern Foods activation code is: ' . $this->account_verification_code;
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

            // Initialize the SDK and get the SMS service
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            // Use the service to send the OTP
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
