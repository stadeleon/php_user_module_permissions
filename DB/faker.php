<?php

use DB\Connection;

require_once 'vendor/autoload.php';

$faker = Faker\Factory::create();

$dbConnection = Connection::getInstance();
$pdo = $dbConnection->getConnection();

for ($i = 0; $i < 10; $i++) {
    $name = $faker->name;
    $stmt = $pdo->prepare("INSERT INTO users (name) VALUES (:name)");
    $stmt->bindValue(':name', $name);
    $stmt->execute();
}

for ($i = 0; $i < 5; $i++) {
    $name = uniqid('group_', true);
    $stmt = $pdo->prepare("INSERT INTO user_groups (name) VALUES (:name)");
    $stmt->bindValue(':name', $name);
    $stmt->execute();
}

$userIds = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
$groupIds = $pdo->query("SELECT id FROM user_groups")->fetchAll(PDO::FETCH_COLUMN);

foreach ($userIds as $userId) {
    $randomGroupId = $faker->randomElement($groupIds);
    $stmt = $pdo->prepare("INSERT INTO user_group (user_id, group_id) VALUES (:user_id, :group_id)");
    $stmt->bindValue(':user_id', $userId);
    $stmt->bindValue(':group_id', $randomGroupId);
    $stmt->execute();
}

$functionIds = [];
$parentIds = [];

for ($i = 0; $i < 200; $i++) {
    $name = uniqid('function_', true);
    $parentId = $faker->randomElement([null, $faker->randomElement($parentIds)]);
    $stmt = $pdo->prepare("INSERT INTO functions (name, parent_id) VALUES (:name, :parent_id)");
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':parent_id', $parentId);
    $stmt->execute();

    $id = $pdo->lastInsertId();

    if ($parentId === null) {
        $parentIds[] = $id;
    }

    $functionIds[] = $id;
}

foreach ($userIds as $userId) {
    $randomFunctionId = $faker->randomElement($functionIds);
    $stmt = $pdo->prepare("INSERT INTO user_function (user_id, function_id) VALUES (:user_id, :function_id)");
    $stmt->bindValue(':user_id', $userId);
    $stmt->bindValue(':function_id', $randomFunctionId);
    $stmt->execute();
}

foreach ($groupIds as $groupId) {
    $randomFunctionId = $faker->randomElement($functionIds);
    $stmt = $pdo->prepare("INSERT INTO user_group_function (group_id, function_id) VALUES (:group_id, :function_id)");
    $stmt->bindValue(':group_id', $groupId);
    $stmt->bindValue(':function_id', $randomFunctionId);
    $stmt->execute();
}