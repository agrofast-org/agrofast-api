<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\Transport\Carrier;
use Ilias\Choir\Model\Transport\Machinery;
use Ilias\Choir\Model\Transport\Offer;
use Ilias\Choir\Model\Transport\Request;
use Ilias\Maestro\Abstract\Schema;

final class Transport extends Schema
{
  public Machinery $machinery;
  public Offer $offer;
  public Request $request;
  public Carrier $carrier;
}
