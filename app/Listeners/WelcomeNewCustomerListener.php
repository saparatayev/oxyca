<?php

namespace App\Listeners;

use App\Mail\WelcomeNewCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class WelcomeNewCustomerListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // in order to test queues using sleep function
        sleep(10);

        Mail::to($event->customer->email)->send(new WelcomeNewCustomerMail);
    }
}
