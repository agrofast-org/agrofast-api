<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\Document;
use Ilias\Choir\Model\DocumentType;
use Ilias\Choir\Model\Profile;
use Ilias\Choir\Model\ProfileType;
use Ilias\Choir\Model\User;
use Ilias\Maestro\Abstract\Schema;

final class Hr extends Schema
{
  public User $user;
  public Profile $profile;
  public ProfileType $profileType;
  public Document $document;
  public DocumentType $documentType;
}
