<?php

namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    /**
     * Nombre de la tabla base del modelo (ej: "users", "posts", "comments").
     */
    protected string $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function getAll(): ?array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        return Database::fetchAll($sql);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        Database::query($sql, ['id' => $id]);
    }

    public function insert(array $data): int
    {
        // $data es algo como ['email' => '...', 'username' => '...', 'password' => '...']
        // Separamos los nombres de las columnas de los valores.
        $columns = [];
        $placeholders = [];

        foreach ($data as $column => $value) {
            $columns[] = $column;
            $placeholders[] = ':' . $column;
        }

        $sql = 'INSERT INTO ' . $this->tableName
            . ' (' . implode(', ', $columns) . ')'
            . ' VALUES (' . implode(', ', $placeholders) . ')';

        // Database::query() recibe la SQL con placeholders (:email, :username…)
        // y el array $data con los valores. Los empareja por nombre.
        Database::query($sql, $data);

        return Database::lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        // $data es algo como ['username' => 'nuevo', 'email' => 'nuevo@...']
        // Construimos la parte "campo = :campo" de la SQL.
        $assignments = [];

        foreach ($data as $column => $value) {
            $assignments[] = $column . ' = :' . $column;
        }

        $sql = 'UPDATE ' . $this->tableName
            . ' SET ' . implode(', ', $assignments)
            . ' WHERE id = :id';

        // El WHERE necesita :id, así que lo añadimos al array de parámetros.
        $data['id'] = $id;

        return Database::query($sql, $data)->rowCount();
    }


}