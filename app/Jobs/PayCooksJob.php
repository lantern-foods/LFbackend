<?php

namespace App\Jobs;

use App\Models\Cook;
use App\Traits\HandlesMpesaTransactions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PayCooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HandlesMpesaTransactions;

    protected $commissionRate;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->commissionRate = 0.1; // 10% commission rate
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $cooks = Cook::with('meals.orders')->get();

        foreach ($cooks as $cook) {
            $amount = $this->calculateCommission($cook);

            if ($amount > 0) {
                $this->processPayment($cook->mpesa_number, $amount);
            }
        }
    }

    /**
     * Calculate the total commission for the cook.
     *
     * @param Cook $cook
     * @return float
     */
    protected function calculateCommission(Cook $cook): float
    {
        $totalSales = 0;

        foreach ($cook->meals as $meal) {
            foreach ($meal->orders as $order) {
                $totalSales += $order->pivot->subtotal; // Assuming subtotal is stored in the pivot table
            }
        }

        return $totalSales * $this->commissionRate;
    }

    /**
     * Process the Mpesa payment for the cook.
     *
     * @param string $phoneNumber
     * @param float $amount
     */
    protected function processPayment(string $phoneNumber, float $amount): void
    {
        try {
            $this->sendMpesaPayment($phoneNumber, $amount, 'Cook commission');
            Log::info("Payment of {$amount} sent to cook with phone number {$phoneNumber}");
        } catch (\Exception $e) {
            Log::error("Error processing payment for {$phoneNumber}: " . $e->getMessage());
        }
    }
}
