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
use Illuminate\Support\Facades\Log;

class CookApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $cookName;
    private $reason;
    private $phoneNumber;

    /**
     * Create a new job instance.
     *
     * @param Cook $cook
     * @param CookApprovalStatus $approval
     */
    public function __construct(Cook $cook, CookApprovalStatus $approval)
    {
        $this->cookName = $cook->kitchen_name;
        $this->reason = $approval->reason;
        $this->phoneNumber = $cook->mpesa_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = $this->composeRejectionMessage();
        $recipient = $this->formatPhoneNumber($this->phoneNumber);

        if ($recipient != 'Invalid' && strlen($recipient) == 12) {
            $this->sendSms($recipient, $message);
        } else {
            Log::error('Invalid phone number: ' . $this->phoneNumber);
        }
    }

    /**
     * Compose the rejection message.
     *
     * @return string
     */
    private function composeRejectionMessage(): string
    {
        return 'Dear ' . $this->cookName . ', your Lantern Foods cook profile was rejected because: ' . $this->reason;
    }

    /**
     * Send SMS using Africa's Talking SDK.
     *
     * @param string $recipient
     * @param string $message
     */
    private function sendSms(string $recipient, string $message): void
    {
        try {
            $username = config('sms.sms.at_username');
            $apiKey = config('sms.sms.at_api_key');
            $from = 'Br_Lantern';  // Sender ID or shortCode

            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();

            $result = $sms->send([
                'to' => $recipient,
                'message' => $message,
                'from' => $from,
            ]);

            Log::info('SMS sent to ' . $recipient, ['response' => $result]);
        } catch (\Exception $e) {
            Log::error('SMS Error: ' . $e->getMessage());
        }
    }
}
