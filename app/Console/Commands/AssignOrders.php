<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\AssignOrdersToNearestRider;

class AssignOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign all ready orders to the nearest available riders';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Dispatch the job
        AssignOrdersToNearestRider::dispatch();

        // Optional: Output a message to the console
        $this->info('The orders have been assigned successfully.');
    }
}
