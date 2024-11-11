<?php

use Ilias\Choir\Database\AgrofastDB;
use Ilias\Dotenv\Environment;
use Ilias\Maestro\Core\Manager;
use Ilias\Maestro\Database\PDOConnection;

require_once("./vendor/autoload.php");

Environment::setup(null, Environment::SUPPRESS_EXCEPTION);

PDOConnection::get(dbHost: "localhost");
$coreDatabase = new Manager();
$agrofastDB = new AgrofastDB();

print implode("\n", $coreDatabase->createDatabase($agrofastDB, true)) . "\n";

// $user = User::fetchAll()[0];
// $user->name = "Murilo Elias";
// $user->save();

// print $coreDatabase->createTable(User::class) . "\n";

// $user = new User('name', 'number','password',true, new Timestamp());
// $insert = new Insert(Maestro::SQL_STRICT, PDOConnection::get());
// $result = $insert->into(User::class)->values($user)->returning(['id'])->execute();

// echo SmsSender::send('+5564996020731', 'Hello, this is a test message') . "\n";
