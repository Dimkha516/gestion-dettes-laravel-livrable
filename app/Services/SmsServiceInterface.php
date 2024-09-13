<?php
namespace App\Services;

interface SmsServiceInterface
{
    public function sendSms(string $to, string $message);
}
