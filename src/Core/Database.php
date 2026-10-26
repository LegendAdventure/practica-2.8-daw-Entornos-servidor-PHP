<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $sharedInstance = null;

    private PDO $connection;

    private function __construct()
    {
        $configuration = require __DIR__ . '/../../config.php';
        $databaseConfig = $configuration['db'];

        $dataSourceName = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $databaseConfig['host'],
            $databaseConfig['dbname'],
            $databaseConfig['charset']
        );

        try {
            $this->connection = new PDO(
                $dataSourceName,
                $databaseConfig['user'],
                $databaseConfig['pass'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $connectionError) {
            die('Error de conexión a la base de datos: ' . $connectionError->getMessage());
        }
    }

    public static function getSharedInstance(): Database
    {
        if (self::$sharedInstance === null) {
            self::$sharedInstance = new self();
        }

        return self::$sharedInstance;
    }

    public static function query(string $sql, array $parameters = []): PDOStatement
    {
        $statement = self::getSharedInstance()->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement;
    }

    public static function fetchAll(string $sql, array $parameters = []): ?array
    {
        $rows = self::query($sql, $parameters)->fetchAll();

        return $rows === [] ? null : $rows;
    }

    public static function fetchOne(string $sql, array $parameters = []): ?array
    {
        $row = self::query($sql, $parameters)->fetch();

        return $row === false ? null : $row;
    }

    public static function lastInsertId(): int
    {
        return (int) self::getSharedInstance()->connection->lastInsertId();
    }
}