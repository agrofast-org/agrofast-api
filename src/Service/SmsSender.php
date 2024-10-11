<?php

namespace Ilias\Choir\Service;

use HTTP_Request2;
use HTTP_Request2_Exception;
use Throwable;

class SmsSender
{
  public static function send($phoneNumber, $message)
  {
    $serviceKey = getenv("SMS_SERVICE_KEY");

    $request = new HTTP_Request2();
    $request->setUrl('https://qdygm3.api.infobip.com/2fa/2/applications');
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $request->setConfig(array(
      'follow_redirects' => TRUE
    ));
    $request->setHeader(array(
      'Authorization' => "App {$serviceKey}",
      'Content-Type' => 'application/json',
      'Accept' => 'application/json'
    ));
    $request->setBody('{"messages":[{"destinations":[{"to":"' . $phoneNumber . '"}],"from":"29175","text":"' . $message . '"}]}');
    $response = $request->send();
    if ($response->getStatus() == 200) {
      return $response->getBody();
    } else {
      throw new \Exception('Unexpected HTTP status: ' . $response->getStatus() . ' ' .
        $response->getReasonPhrase());
    }
  }
}
