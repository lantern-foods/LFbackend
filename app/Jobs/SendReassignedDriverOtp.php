<?php

namespace App\Jobs;

use App\Models\Driver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use AfricasTalking\SDK\AfricasTalking;
use App\Traits\GlobalFunctions;

class SendReassignedDriverOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $driver;
    protected $otp;
    /**
     * Create a new job instance.
     */
    public function __construct(Driver $driver, $otp)
    {
        $this->driver = $driver;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Construct the message with OTP and terms
        $message = "Dear " . $this->driver->driver_name . ", your reassignment OTP is: " . $this->otp . 
                   ". Do not share your code. Read the terms and conditions.";

        // Attempt to format the driver's phone number
        $recipient = $this->formatPhoneNumber($this->driver->phone_number);

        if ($recipient != 'Invalid' && strlen($recipient) == 12) {
            $username = config('sms.africastalking.username'); // Ensure these are correctly set in your .env or config files
            $apiKey = config('sms.africastalking.apiKey');
            
            // Initialize the AfricasTalking SDK
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            try {
                // Attempt to send the SMS
                $result = $sms->send([
                    'to' => $recipient,
                    'message' => $message,
                    'from' => "Br_Lantern", // Your sender ID
                ]);
            } catch (\Exception $e) {
                \Log::error("SMS Error: " . $e->getMessage());
            }
        } else {
            \Log::error("Sending OTP: Invalid phone number " . $this->driver->phone_number);
        }
    }
}
