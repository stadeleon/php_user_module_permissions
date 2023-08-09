<?php

require_once 'vendor/autoload.php';

use DB\Connection;
use App\Permissions;

$dbConnection = Connection::getInstance();
$pdo = $dbConnection->getConnection();

$permissions = new Permissions($pdo);

$userName = $argv[1];
$function = $argv[2];

if (array_key_exists(3, $argv) and 'list' === $argv[3]) {
    $permissions->printUsersFunctionsList();
}

$isFunctionAllowed = $permissions->isUserFunctionAllowed($userName, $function);

echo $isFunctionAllowed ? "Function Allowed \n" : "Function Denied \n";