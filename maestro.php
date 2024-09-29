<?php
use Ilias\Choir\Database\AgrofastDB;
use Ilias\Maestro\Core\Manager;

require_once("./vendor/autoload.php");

$coreDatabase = new Manager();
$agrofastDB = new AgrofastDB();

print implode("\n", $coreDatabase->createDatabase($agrofastDB, false)) . "\n";
  