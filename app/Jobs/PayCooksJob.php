<?php

namespace App\Jobs;

use App\Models\Cook;
use App\Traits\HandlesMpesaTransactions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PayCooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesMpesaTransactions;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $cooks = Cook::with('meals.orders')->get();

        foreach ($cooks as $cook) {
            $phoneNumber = $cook->mpesa_number;
            $amount = $this->calculateCommission($cook);
            if ($amount > 0) {
                $this->sendMpesaPayment($phoneNumber, $amount, 'Cook commission');
                
            }
        }
    }

    protected function calculateCommission(Cook $cook)
    {
        $commissionRate = 1.1; // 10%
        $totalSales = 0;

        foreach ($cook->meals as $meal) {
            foreach ($meal->orders as $order) {
                $totalSales += $order->pivot->subtotal;
            }
        }

        return $totalSales * $commissionRate;
    }
}
