<?php

namespace App\Notifications;

use App\Models\DemandeDeDette;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DebtRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $demande;
    public function __construct(DemandeDeDette $demande)
    {
        $this->demande = $demande;
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
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvelle demande de dette')
            ->line('Un client a soumis une nouvelle demande de dette.')
            ->line('Client : ' . $this->demande->client->surname)
            ->line('Montant total : ' . $this->demande->montant_total)
            ->line('Articles : ' . json_encode($this->demande->articles))
            ->action('Voir la demande', url('/demandes_de_dette/' . $this->demande->id));
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
