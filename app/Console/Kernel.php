<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TestMercadoPago::class,
        Commands\TestPaymentQR::class,
        Commands\GenerateSingleInvoice::class,
        Commands\QuickInvoice::class,
        Commands\ListUsers::class,
        Commands\CheckQRCodes::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
       
       // actions for backup system
       // $schedule->command('backup:clean')->daily()->at('01:00');
       // $schedule->command('backup:run')->daily()->at('18:50');
       
       // $schedule->command('backup:run');
       $schedule->command('backup:clean');
       $schedule->command('backup:run --only-db');
       
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
