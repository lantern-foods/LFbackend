<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shift;
use Carbon\Carbon;

class AutoEndShifts extends Command
{
    // The name and signature of the console command
    protected $signature = 'shifts:auto-end';

    // The console command description
    protected $description = 'Auto-end shifts when the end time is reached';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the current time
        $now = Carbon::now();

        // Find all active shifts (shift_status = 1) where the end_time has passed
        $shifts = Shift::where('shift_status', 1)
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($shifts as $shift) {
            // Set the shift status to inactive
            $shift->shift_status = 0;
            $shift->save();

            // Log or display a message that the shift was ended
            $this->info('Shift ID ' . $shift->id . ' has been ended as per the end time.');
        }

        return 0;
    }
}
