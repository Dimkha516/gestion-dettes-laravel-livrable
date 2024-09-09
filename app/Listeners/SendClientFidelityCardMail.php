<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\ClientFidelityCardMail;
use Illuminate\Support\Facades\Mail;

class SendClientFidelityCardMail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClientCreated $event)
    {
        if ($event->client && $event->user && $event->pdfPath) {

            Mail::to($event->user->email)->send(new ClientFidelityCardMail($event->client, $event->user, $event->pdfPath));
        }
    }
}
