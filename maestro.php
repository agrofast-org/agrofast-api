<?php
use Ilias\Choir\Database\AgrofastDB;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Core\Maestro;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\Insert;
use Ilias\Maestro\Database\PDOConnection;
use Ilias\Maestro\Types\Timestamp;

require_once("./vendor/autoload.php");

$coreDatabase = new Manager();
$agrofastDB = new AgrofastDB();

print implode("\n", $coreDatabase->createDatabase($agrofastDB, false)) . "\n";
// print $coreDatabase->createTable(User::class) . "\n";

// $user = new User('name', 'number','password',true, new Timestamp());
// $insert = new Insert(Maestro::SQL_STRICT, PDOConnection::get());
// $result = $insert->into(User::class)->values($user)->returning(['id'])->execute();
