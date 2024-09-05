<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;


class ClientFidelityCardMail extends Mailable
{
    use Queueable, SerializesModels;

    public $client;
    public $user;
    public $pdfPath;
    /**
     * Create a new message instance.
     */
    public function __construct($client, $user, $pdfPath)
    {
        $this->client = $client;
        $this->user = $user;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Votre carte de fidélité')
            ->view('emails.fidelity_card')
            ->attach(Storage::disk('local')->path($this->pdfPath)) // Ajouter le PDF en pièce jointe
            ->with([
                'pseudo' => $this->user->pseudo,
                'email' => $this->user->email,
                'clientName' => $this->client->surname,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Client Fidelity Card Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
