<?php

error_reporting(E_ALL);

use Ilias\Choir\Bootstrap\Core;

require_once("./vendor/autoload.php");

set_error_handler(Core::class . "::errorHandler");

Core::handle();