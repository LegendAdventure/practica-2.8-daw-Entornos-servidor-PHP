<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * Instancia única de la clase (patrón singleton).
     * null mientras no se haya creado todavía.
     */
    private static ?Database $sharedInstance = null;

    /**
     * Conexión PDO reutilizable.
     */
    private PDO $connection;

    /**
     * Constructor privado: nadie puede hacer "new Database()" desde fuera.
     * Solo se instancia una vez, desde getSharedInstance().
     */
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

    /**
     * Devuelve la única instancia de Database de toda la petición.
     * La crea la primera vez; las siguientes, la reutiliza.
     */
    public static function getSharedInstance(): Database
    {
        if (self::$sharedInstance === null) {
            self::$sharedInstance = new self();
        }

        return self::$sharedInstance;
    }

    /**
     * Prepara y ejecuta una consulta SQL con parámetros vinculados.
     * Devuelve el PDOStatement ya ejecutado.
     */
    public static function query(string $sql, array $parameters = []): PDOStatement
    {
        $statement = self::getSharedInstance()->connection->prepare($sql);
        $statement->execute($parameters);

        return $statement;
    }

    /**
     * Ejecuta una consulta y devuelve todos los registros.
     * Devuelve null si no hay resultados.
     */
    public static function fetchAll(string $sql, array $parameters = []): ?array
    {
        $rows = self::query($sql, $parameters)->fetchAll();

        return $rows === [] ? null : $rows;
    }

    /**
     * Ejecuta una consulta y devuelve el primer registro.
     * Devuelve null si no hay resultados.
     */
    public static function fetchOne(string $sql, array $parameters = []): ?array
    {
        $row = self::query($sql, $parameters)->fetch();

        return $row === false ? null : $row;
    }
}