<?php

namespace App\Notifications;

use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelDetteSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $client;
    protected $dette;


    /**
     * Create a new notification instance.
     */
    public function __construct($client, $dette)
    {
        $this->client = $client;
        $this->dette = $dette;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['sms'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toSms($notifiable)
    {
        $message = "Bonjour {$this->client->nom}, vous avez une dette de {$this->dette->montantRestant} CFA. Merci de la régler dès que possible.
        (Ne répondez pas à ce message. C'est juste un test d'application. BY B@mb@)";
        return (new TwilioService())->sendSms($this->client->telephone, $message);
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
