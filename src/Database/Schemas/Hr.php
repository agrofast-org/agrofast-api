<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\Hr\AuthCode;
use Ilias\Choir\Model\Hr\ContactType;
use Ilias\Choir\Model\Hr\Document;
use Ilias\Choir\Model\Hr\DocumentType;
use Ilias\Choir\Model\Hr\Session;
use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Hr\UserSettings;
use Ilias\Maestro\Abstract\Schema;

final class Hr extends Schema
{
  public AuthCode $authCode;
  public ContactType $contactType;
  public Document $document;
  public DocumentType $documentType;
  public Session $session;
  public User $user;
  public UserSettings $userSettings;
}
