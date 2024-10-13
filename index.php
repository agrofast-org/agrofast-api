<?php

error_reporting(E_ERROR | E_PARSE);

use Ilias\Choir\Bootstrap\Core;
use Ilias\Choir\Model\Hr\User;

require_once("./vendor/autoload.php");

set_error_handler(Core::class . "::errorHandler");

Core::handle();
