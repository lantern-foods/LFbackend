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

class SendDriverOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $driver_name;
    private $account_verification_code;
    private $phone_no;

    protected $driver;

    /**
     * Create a new job instance.
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
        // Send account activation code
        $message = 'Dear ' . $this->driver_name . ', your Lantern Foods activation code is: ' . $this->account_verification_code;

        $recipient = $this->formatPhoneNumber($this->phone_no);

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
                        'to' => $recipient,
                        'message' => $message,
                        'from' => $from
                    ]);
                } catch (Exception $e) {
                    \Log::error('SMS Error: ' . $e->getMessage());
                }
            } else {
                \Log::error('Sending activation code: Invalid phone number ' . $this->phone_no);
            }
        } else {
            \Log::error('Sending activation code: Invalid phone number ' . $this->phone_no);
        }
    }
}
