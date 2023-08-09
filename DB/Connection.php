<?php

namespace DB;
use PDO;

class Connection
{
    private static $instance = null;

    private $dbHost;
    private $charset = 'utf8mb4';
    private $dbName;
    private $userName;
    private $password;

    private $pdo;

    private function __construct()
    {
        $this->setCredentials();

        $dsn = "mysql:host=$this->dbHost;dbname=$this->dbName;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->userName, $this->password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new Connection();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function setCredentials(): void
    {
        if (!isset($_ENV['MYSQL_HOST'])) {
            throw new \Exception('Environment variable MYSQL_HOST is not set.');
        }

        if (!isset($_ENV['MYSQL_DATABASE'])) {
            throw new \Exception('Environment variable MYSQL_DATABASE is not set.');
        }

        if (!isset($_ENV['MYSQL_USER'])) {
            throw new \Exception('Environment variable MYSQL_USER is not set.');
        }

        if (!isset($_ENV['MYSQL_PASSWORD'])) {
            throw new \Exception('Environment variable MYSQL_PASSWORD is not set.');
        }

        $this->dbHost =  $_ENV['MYSQL_HOST'];
        $this->dbName =  $_ENV['MYSQL_DATABASE'];
        $this->userName =  $_ENV['MYSQL_USER'];
        $this->password =  $_ENV['MYSQL_PASSWORD'];
    }
}