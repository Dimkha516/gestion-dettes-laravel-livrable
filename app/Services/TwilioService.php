<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $twilio;
    protected $twilioClient;


    public function __construct()
    {
        // $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->twilioClient = new Client($sid, $token);
    }

    public function sendSms($to, $message)
    {   
        $from = config('services.twilio.from');

        return $this->twilioClient->messages->create($to, [
            'from' => $from,
            'body' => $message,
        ]);
        // $this->twilio->messages->create($to, [
        //     'from' => env('TWILIO_PHONE_NUMBER'),
        //     'body' => $message,
        // ]);
    }
}
