<?php

namespace App\Jobs;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;

class SendReassignedDriverOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driver;
    protected $otp;

    /**
     * Create a new job instance.
     *
     * @param  Driver  $driver
     * @param  string  $otp
     */
    public function __construct(Driver $driver, string $otp)
    {
        $this->driver = $driver;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Construct the reassignment OTP message
        $message = $this->buildMessage();
        $recipient = $this->formatPhoneNumber($this->driver->phone_number);

        if ($this->isValidPhoneNumber($recipient)) {
            $this->sendOtp($recipient, $message);
        } else {
            Log::error("Sending OTP: Invalid phone number " . $this->driver->phone_number);
        }
    }

    /**
     * Build the OTP message.
     *
     * @return string
     */
    private function buildMessage(): string
    {
        return "Dear " . $this->driver->driver_name . ", your reassignment OTP is: " . $this->otp .
               ". Do not share your code. Read the terms and conditions.";
    }

    /**
     * Validate the phone number format.
     *
     * @param  string  $phone_no
     * @return bool
     */
    private function isValidPhoneNumber(string $phone_no): bool
    {
        return strlen($phone_no) === 12; // Validate phone number length (12 digits)
    }

    /**
     * Send the OTP via Africa's Talking API.
     *
     * @param  string  $recipient
     * @param  string  $message
     * @return void
     */
    private function sendOtp(string $recipient, string $message): void
    {
        try {
            $username = config('sms.africastalking.username'); // Ensure these are correctly set
            $apiKey = config('sms.africastalking.apiKey');
            $from = "Br_Lantern"; // Sender ID

            // Initialize the SDK
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            // Send the SMS
            $sms->send([
                'to' => $recipient,
                'message' => $message,
                'from' => $from,
            ]);

            Log::info('Reassignment OTP sent to ' . $recipient);
        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
        }
    }
}
