<?php

// app/Services/InfobipService.php

namespace App\Services;

use GuzzleHttp\Client;

class InfoBipTestService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        // $this->apiKey = config('services.infobip.api_key');
        // $this->baseUrl = config('services.infobip.base_url');
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->baseUrl = env('INFOBIP_BASE_URL');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function sendMessage($to, $message)
    {
        // $response = $this->client->post('/sms/2/text/advanced', [
            $response = $this->client->post('z3j382.api.infobip.com', [

        'json' => [
                'messages' => [
                    [
                        'from' => 'InfoSMS',
                        'destinations' => [
                            [
                                'to' => $to,
                            ],
                        ],
                        'text' => $message,
                    ],
                ],
            ],
        ]);

        return $response->getBody()->getContents();
    }
}
