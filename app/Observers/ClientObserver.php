<?php

namespace App\Observers;

use App\Models\Client;
use App\Jobs\GenerateQRCode;
use App\Jobs\GeneratePDF;


class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client)
    {
        if ($client->user) {
            GenerateQRCode::dispatch($client);
            GeneratePDF::dispatch($client, $client->user, 'qrcodes/clients/' . $client->surname . '_' . $client->id . '.png', $client->photo);
        }
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void 
    {

    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        //
    }
}
