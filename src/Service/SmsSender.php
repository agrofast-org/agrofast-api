<?php

namespace Ilias\Choir\Service;

use HTTP_Request2;
use HTTP_Request2_Exception;
use Throwable;
use Twilio\Rest\Client;

class SmsSender
{
  public static function send($phoneNumber, $message)
  {
    // $serviceKey = getenv("SMS_SERVICE_KEY");

    // $request = new HTTP_Request2();
    // $request->setUrl('https://qdygm3.api.infobip.com/2fa/2/applications');
    // $request->setMethod(HTTP_Request2::METHOD_POST);
    // $request->setConfig(array(
    //   'follow_redirects' => TRUE
    // ));
    // $request->setHeader(array(
    //   'Authorization' => "App {$serviceKey}",
    //   'Content-Type' => 'application/json',
    //   'Accept' => 'application/json'
    // ));
    // $request->setBody('{"messages":[{"destinations":[{"to":"' . $phoneNumber . '"}],"from":"29175","text":"' . $message . '"}]}');
    // $response = $request->send();
    // if ($response->getStatus() == 200) {
    //   return $response->getBody();
    // } else {
    //   throw new \Exception('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
    //     $response->getReasonPhrase());
    // }

    $sid = "ACfe7dca55f96a7029ce039ed2e6501ba1";
    $token = getenv("SMS_SERVICE_KEY");
    $twilio = new Client($sid, $token);
    $message = $twilio->messages
      ->create(
        $phoneNumber,
        [
          "messagingServiceSid" => "MG6197d300e92531acb13661a8c4e13daa",
          "body" => $message
        ]
      );
    print ($message->sid);
  }
}
