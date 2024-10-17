<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Document extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public DocumentType|int $documentType;
  /** @unique */
  public string $document;

  public function compose(string $document)
  {
    $this->document = $document;
  }
}
