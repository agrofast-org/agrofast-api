<?php

error_reporting(E_ERROR | E_PARSE);

use Ilias\Choir\Bootstrap\Core;

require_once("./vendor/autoload.php");

set_error_handler(Core::class . "::errorHandler", E_ERROR | E_PARSE);

Core::handle();
