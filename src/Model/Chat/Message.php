<?php

namespace Ilias\Choir\Model\Chat;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\Select;
use Ilias\Maestro\Database\Transaction;
use Ilias\Maestro\Types\Serial;

final class Message extends TrackableTable
{
  public Chat $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public Chat|int $chatId;
  /** @nuable */
  public Message|int|null $answerTo = null;
  /** @not_nuable */
  public string $message;

  public function compose(
    int $userId,
    int $chatId,
    string $message
  ): void {
    $this->userId = $userId;
    $this->chatId = $chatId;
    $this->message = $message;
  }

  public static function sendMessage(int $userId, int $chatId, string $message, int $answerTo = null): self
  {
    $message = new self($userId, $chatId, $message);
    if (!empty($answerTo)) {
      $message->answerTo = $answerTo;
    }
    $insert = new Insert();
    $insert->into(Message::class)
      ->values($message)
      ->returning(['id']);
    $transaction = new Transaction();
    $transaction->begin();
    try {
      $result = $insert->execute()[0];
      $transaction->commit();
      $message->id = $result['id'];
      return $message;
    } catch (\Throwable) {
      $transaction->rollback();
      throw new \Exception('Failed to send message');
    }
  }

  public static function getUserChats(int $userId)
  {
    $subSelect = new Select();
    $subSelect->from(['m' => Message::class], ['from_user_id', 'last_message_time' => new Expression("MAX(created_in)")])
      ->group(['from_user_id']);
    $select = new Select();
    $select->from(['m' => Message::class], ['message', 'from_user_id'])
      ->join(['u' => User::class], 'u.id = m.to_user_id', ['name'])
      ->join(['lm' => $subSelect], 'm.from_user_id = lm.from_user_id AND m.created_in = lm.last_message_time', [])
      ->where(['m.to_user_id' => $userId]);
    return $select->execute() ?? null;
  }
}

/*
SELECT m.message, m.from_user_id, u.name
FROM chat.message m
INNER JOIN hr."user" u ON u.id = m.from_user_id
INNER JOIN (
    SELECT from_user_id, MAX(
            created_in) AS last_message_time
    FROM chat.message
    GROUP BY from_user_id
) AS lm ON m.from_user_id = lm.from_user_id AND m.created_in = lm.last_message_time
WHERE m.to_user_id = 6;
*/