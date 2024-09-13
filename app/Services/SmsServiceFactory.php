<?php
namespace App\Services;
use App\Services\TwilioSmsService;
use App\Services\InfoBipSmsService;

class SmsServiceFactory{
    public static function getProvider(): SmsServiceInterface{
        
        $provider = config('sms.provider');

        if($provider === 'infobip'){
            return new InfoBipSmsService();
        }

        return new TwilioSmsService();

    }
}