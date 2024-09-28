<?php

namespace Ilias\Choir\Model;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Document extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
  /** @not_nuable */
  public DocumentType $documentType;
  /** @unique */
  public string $document;

  public function __construct(string $document)
  {
    $this->document = $document;
  }
}
