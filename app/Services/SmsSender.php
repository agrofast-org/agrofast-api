<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic as VonageCredentials;
use Vonage\SMS\Message\SMS as VonageSMS;

class SmsSender
{
    /**
     * Send an SMS message using Vonage.
     *
     * @param string $phoneNumber
     * @param string $message
     */
    public static function send(string $phoneNumber, string $message)
    {
        $key = env('VONAGE_KEY');
        $secret = env('VONAGE_SECRET');

        $credentials = new VonageCredentials($key, $secret);
        $client = new VonageClient($credentials);

        try {
            $response = $client->sms()->send(
                new VonageSMS($phoneNumber, 'Agrofast', $message)
            );

            $messageResponse = $response->current();

            if ($messageResponse->getStatus() == 0) {
                Log::info("Message sent successfully to {$phoneNumber}");
            } else {
                Log::error("Failed to send message to {$phoneNumber}: ".$messageResponse->getStatus());
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending SMS: '.$e->getMessage());
        }
    }
}
