<?php


namespace App\Services;

use Twilio\Rest\Client;
use App\Services\SmsServiceInterface;

class TwilioSmsService implements SmsServiceInterface
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }

    public function sendSms(string $to, string $message)
    {
        $this->twilio->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message
        ]);
    }
}
