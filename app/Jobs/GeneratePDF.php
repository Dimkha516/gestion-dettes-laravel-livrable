<?php

namespace App\Jobs;

use App\Events\ClientCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;


class GeneratePDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $user;
    protected $qrPath;
    protected $photoUrl;

    /**
     * Create a new job instance.
     */
    public function __construct($client, $user, $qrPath, $photoUrl)
    {
        $this->client = $client;
        $this->user = $user;
        $this->qrPath = $qrPath;
        $this->photoUrl = $photoUrl;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->client && $this->user) {
            $pdf = Pdf::loadView('fidelite_card', [
                'pseudo' => $this->user->pseudo,
                'email' => $this->user->email,
                'qrCodePath' => $this->qrPath,
                'photoUrl' => $this->photoUrl
            ]);

            $pdfPath = 'fidelite_cards/' . $this->client->surname . '_' . $this->client->id . '.pdf';
            Storage::disk('local')->put($pdfPath, $pdf->output());

            event(new ClientCreated($this->client, $this->user, $pdfPath));
        }
    }
}
