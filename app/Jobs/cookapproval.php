<?php

namespace App\Jobs;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\Cook;
use App\Models\CookApprovalStatus;
use App\Traits\GlobalFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class cookapproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $cook_name;
    private $reason;
    private $phone_no;

    protected $cook;

    /**
     * Create a new job instance.
     * @param  Cook  $cook
     * @return void
     */
    public function __construct(Cook $cook, CookApprovalStatus $approval)
    {
        $this->cook_name = $cook->kitchen_name;
        $this->reason = $approval->reason;
        $this->phone_no = $cook->mpesa_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send account activation code
        $message = 'Dear ' . $this->cook_name . ', your Lantern Foods cook profile was rejected because of this reason: ' . $this->reason;

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
