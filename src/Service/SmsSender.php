<?php

namespace Ilias\Choir\Service;

class SmsSender
{
  public static function send($phoneNumber, $message)
  {
    $key = getenv('SMS_SERVICE_KEY');
    $secret = getenv('SMS_SERVICE_SECRET');
    
    $basic = new \Vonage\Client\Credentials\Basic($key, $secret);
    $client = new \Vonage\Client($basic);

    $response = $client->sms()->send(
      new \Vonage\SMS\Message\SMS($phoneNumber, 'Agrofast', $message)
    );

    // $result = $response->current();

    // if ($result->getStatus() == 0) {
    //   echo "The message was sent successfully\n";
    // } else {
    //   echo "The message failed with status: " . $result->getStatus() . "\n";
    // }
  }
}
