<?php

namespace Ilias\Choir\Model\Chat;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
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
  public User|int $fromUserId;
  /** @not_nuable */
  public User|int $toUserId;
  /** @nuable */
  public Message|int|null $answerTo = null;
  /** @not_nuable */
  public string $message;
  public bool $isRead = false;

  public function compose(string $message): void
  {
    $this->message = $message;
  }

  public static function sendMessage(int $fromUserId, int $toUserId, string $message, int $answerTo = null): self
  {
    $message = new self($fromUserId, $toUserId, $message);
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
    $select = new Select();
    $select->from(['m'=> Message::class])
      ->where(['to_user_id' => $userId])
      ->group(['from_user_id'])
      ->order('created_at', 'desc');
  }
}
