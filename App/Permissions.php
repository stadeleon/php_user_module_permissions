<?php

namespace App;

use PDO;

class Permissions
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getUserFunctionAccess($userName, $function): array
    {
        // Here we prepare temporary table by selecting functions which has searchable value and
        // union with all Functions related by parent_id
        // next we filter by function name and select from temporary table by joining data through pivot tables
        // and return one row, so if user can access current Function it's name will be in result
        $query = "
            WITH RECURSIVE function_hierarchy AS (
            SELECT id, parent_id, name FROM functions
            WHERE name = :function_name
            UNION ALL
            SELECT f.id, f.parent_id, fh.name FROM functions f
                INNER JOIN function_hierarchy fh ON f.parent_id = fh.id
            ) 
            SELECT (
                SELECT id FROM functions WHERE name = fh.name
            ) AS id, fh.name FROM function_hierarchy fh
                LEFT JOIN user_function uf ON fh.id = uf.function_id
                LEFT JOIN user_group ug ON uf.user_id = ug.user_id
                LEFT JOIN user_group_function ugf ON ug.group_id = ugf.group_id AND fh.id = ugf.function_id
                LEFT JOIN users u ON uf.user_id = u.id
            WHERE (u.name = :user_name OR u.id IS NULL) LIMIT 1;
        ";

        $request = $this->pdo->prepare($query);
        $request->bindParam(':user_name', $userName);
        $request->bindParam(':function_name', $function);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isUserFunctionAllowed(string $userName, string $function): bool
    {
        return (bool) count($this->getUserFunctionAccess($userName, $function));
    }

    public function printUsersFunctionsList(): void
    {
        $usersFunctionsList = $this->getUsersFunctionsList();

        foreach ($usersFunctionsList as $row) {
            echo "{$row['user_id']} \t {$row['user_name']} \t {$row['id']} \t {$row['name']} \n";
        }
    }

    private function getUsersFunctionsList(): array
    {
        $query = "
            WITH RECURSIVE function_hierarchy AS (
                SELECT id, parent_id, name FROM functions
                WHERE id IN (SELECT function_id FROM user_function WHERE user_id IN (
                    SELECT id FROM users
                ))
                UNION ALL
                SELECT f.id, f.parent_id, fh.name FROM functions f
                    INNER JOIN function_hierarchy fh ON f.parent_id = fh.id
            ) 
            SELECT DISTINCT u.id AS user_id, u.name AS user_name, fh.id, fh.name 
            FROM function_hierarchy fh
                CROSS JOIN users u
                LEFT JOIN user_function uf ON fh.id = uf.function_id AND u.id = uf.user_id
                LEFT JOIN user_group ug ON uf.user_id = ug.user_id
                LEFT JOIN user_group_function ugf ON ug.group_id = ugf.group_id AND fh.id = ugf.function_id
            ORDER BY u.id;
        ";

        $request = $this->pdo->prepare($query);
        $request->execute();

        return $request->fetchAll(PDO::FETCH_ASSOC);
    }
}
