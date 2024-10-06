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

class SendDeliverycompanyOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GlobalFunctions;

    private $company_name;
    private $account_verification_code;
    private $phone_no;

    protected $deliverycompany;

    /**
     * Create a new job instance.
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
        //Send account activation code
        $message="Dear ".$this->company_name.", your Lantern Foods activation code is: ".$this->account_verification_code;

        $recipient = $this->formatPhoneNumber($this->phone_no);

        if($recipient!='Invalid'){

            if(strlen($recipient)==12){
                $username = config('sms.sms.at_username');
                $apiKey   = config('sms.sms.at_api_key');

                // Set your shortCode or senderId
                $from = "Br_Lantern";
                
                // Initialize the SDK
                $AT = new AfricasTalking($username, $apiKey);

                // Get the SMS service
                try {
                    $sms = $AT->sms();

                    // Use the service
                    $result = $sms->send([
                        'to'      => $recipient,
                        'message' => $message,
                        'from'    => $from
                    ]);
                } catch (Exception $e) {
                    \Log::error("SMS Error: ".$e->getMessage());
                }
            }else{
                \Log::error("Sending activation code: Invalid phone number ".$this->phone_no);
            }
        }else{
            \Log::error("Sending activation code: Invalid phone number ".$this->phone_no);
        }
    }
}
