<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;

class DeleteExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete reservations that have expired (expires_at in the past)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Reservation::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
        $this->info("Deleted $count expired reservations.");
    }
} 