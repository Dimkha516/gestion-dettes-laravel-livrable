<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelDetteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $client;
    protected $montantDu;
    protected $montantRestant;
    public function __construct($client, $montantDu, $montantRestant)
    {
        $this->client = $client;
        $this->montantDu = $montantDu;
        $this->montantRestant = $montantRestant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rappel de votre dette non soldée')
            ->greeting('Bonjour ' . $this->client->nom)
            ->line("Vous avez une dette totale de {$this->montantDu} FCFA.")
            ->line("Montant restant à payer : {$this->montantRestant} FCFA.")
            ->line('Merci de régler votre dette dès que possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
