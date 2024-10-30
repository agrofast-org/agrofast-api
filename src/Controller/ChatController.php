<?php

namespace Ilias\Choir\Controller;

use Ilias\Choir\Model\Chat\Message;
use Ilias\Choir\Model\Hr\User;
use Ilias\Opherator\JsonResponse;
use Ilias\Opherator\Request\StatusCode;

class ChatController
{
  public function getUserChats()
  {
    $user = User::getAuthenticatedUser();
    $chats = Message::getUserChats($user->id);
    return new JsonResponse(new StatusCode(StatusCode::OK), ["data" => $chats]);
  }
}
