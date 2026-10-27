<?php

namespace App\Core;
// Namespace de las clases "core" (núcleo) de la aplicación.
// Composer (PSR-4) lo busca en src/Core/.

use PDO;
use PDOException;
use PDOStatement;
// Importa las clases de PDO que se usan:
//   PDO          → la clase que representa la conexión a la BD.
//   PDOException → la excepción que lanza PDO si algo falla.
//   PDOStatement → el objeto que devuelve prepare()/query() (una sentencia preparada).

class Database
{
    /**
     * Instancia única de Database (patrón singleton).
     * null mientras no se haya creado todavía.
     * static → pertenece a la clase, no a una instancia concreta.
     * ?Database → puede ser Database o null.
     */
    private static ?Database $sharedInstance = null;

    /**
     * Conexión PDO reutilizable.
     * private → solo accesible desde dentro de esta clase.
     */
    private PDO $connection;

    /**
     * Constructor privado: nadie puede hacer "new Database()" desde fuera.
     * Solo se instancia una vez, desde getSharedInstance().
     *
     * Si fuera público, cualquiera podría crear varias conexiones PDO,
     * lo que rompería el patrón singleton.
     */
    private function __construct()
    {
        // 1. Carga la configuración desde config.php.
        //    config.php devuelve un array con las credenciales de la BD.
        //    __DIR__ es src/Core, así que ../../config.php es la raíz del proyecto.
        $configuration = require __DIR__ . '/../../config.php';
        $databaseConfig = $configuration['db'];

        // 2. Construye el DSN (Data Source Name): la "dirección" de la BD.
        //    sprintf sustituye los %s por los valores en orden:
        //    mysql:host=db;dbname=practica;charset=utf8mb4
        $dataSourceName = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $databaseConfig['host'],
            $databaseConfig['dbname'],
            $databaseConfig['charset']
        );

        try {
            // 3. Crea la conexión PDO.
            $this->connection = new PDO(
                $dataSourceName,
                $databaseConfig['user'],
                $databaseConfig['pass'],
                [
                    // 3a. Modo de errores: lanzar excepción en vez de silencioso.
                    //     Si hay un error SQL, se lanza PDOException y se puede capturar.
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // 3b. Modo de fetch por defecto: array asociativo.
                    //     fetch() y fetchAll() devuelven ['id' => 1, 'email' => ...]
                    //     en vez de índices numéricos + asociativos.
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // 3c. Sentencias preparadas REALES del servidor MySQL.
                    //     Con false, PDO no emula la preparación en PHP;
                    //     envía la SQL con placeholders al servidor y MySQL la prepara.
                    //     Es más seguro (no permite reutilizar :nombre varias veces)
                    //     y más eficiente.
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $connectionError) {
            // 4. Si falla la conexión, para la ejecución con un mensaje.
            //    En producción se haría logging; aquí die() es suficiente.
            die('Error de conexión a la base de datos: ' . $connectionError->getMessage());
        }
    }

    /**
     * Devuelve la única instancia de Database de toda la petición.
     * La crea la primera vez; las siguientes, la reutiliza.
     *
     * Esto es el patrón singleton: garantiza una sola conexión PDO por petición.
     */
    public static function getSharedInstance(): Database
    {
        // Si aún no hay instancia, la crea.
        if (self::$sharedInstance === null) {
            self::$sharedInstance = new self();
        }

        // Devuelve la instancia (nueva o reutilizada).
        return self::$sharedInstance;
    }

    /**
     * Prepara y ejecuta una consulta SQL con parámetros.
     * Devuelve el PDOStatement ya ejecutado.
     *
     * Uso: Database::query('SELECT * FROM users WHERE id = :id', ['id' => 1]);
     */
    public static function query(string $sql, array $parameters = []): PDOStatement
    {
        // 1. Obtiene la instancia única y prepara la sentencia.
        //    prepare() compila la SQL con placeholders (:id, :email, etc.).
        //    No ejecuta todavía.
        $statement = self::getSharedInstance()->connection->prepare($sql);

        // 2. Ejecuta la sentencia pasando los parámetros.
        //    Aquí es donde se vinculan los valores a los placeholders.
        //    Los valores NUNCA se concatenan a la SQL → sin SQL injection.
        $statement->execute($parameters);

        // 3. Devuelve el statement para que el llamador pueda hacer
        //    fetch(), fetchAll(), rowCount(), etc.
        return $statement;
    }

    /**
     * Ejecuta una consulta y devuelve TODOS los registros.
     * Devuelve null si no hay resultados.
     *
     * Uso: $users = Database::fetchAll('SELECT * FROM users');
     */
    public static function fetchAll(string $sql, array $parameters = []): ?array
    {
        // 1. Ejecuta la consulta y obtiene todas las filas.
        //    fetchAll() devuelve [] si no hay resultados (nunca null).
        $rows = self::query($sql, $parameters)->fetchAll();

        // 2. Si no hay filas, devuelve null (según la firma ?array).
        //    Si hay, devuelve el array.
        return $rows === [] ? null : $rows;
    }

    /**
     * Ejecuta una consulta y devuelve el PRIMER registro.
     * Devuelve null si no hay resultados.
     *
     * Uso: $user = Database::fetchOne('SELECT * FROM users WHERE id = :id', ['id' => 1]);
     */
    public static function fetchOne(string $sql, array $parameters = []): ?array
    {
        // 1. Ejecuta la consulta y obtiene la primera fila.
        //    fetch() devuelve false si no hay resultados.
        $row = self::query($sql, $parameters)->fetch();

        // 2. Si no hay fila, devuelve null (según la firma ?array).
        //    Si hay, devuelve el array.
        return $row === false ? null : $row;
    }

    /**
     * Devuelve el id del último registro insertado (AUTO_INCREMENT).
     * Útil para que insert() del BaseModel devuelva el id del nuevo registro.
     *
     * lastInsertId() es un método del PDO, no del PDOStatement.
     * Por eso este método vive aquí: solo Database tiene acceso al PDO interno.
     */
    public static function lastInsertId(): int
    {
        // (int) castea el string que devuelve PDO a entero.
        return (int) self::getSharedInstance()->connection->lastInsertId();
    }
}