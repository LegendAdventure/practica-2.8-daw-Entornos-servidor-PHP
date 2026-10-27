<?php

declare(strict_types=1);
// Modo estricto de tipos: si una función espera string y le pasas int,
// lanza TypeError en vez de convertirlo. Buena práctica.

namespace App\Models;
// Namespace de los modelos. Composer (PSR-4) lo busca en src/Models/.

class Comment extends BaseModel
{
    // Hereda de BaseModel, que ya implementa:
    //   - getAll(): ?array
    //   - getById(int $id): ?array
    //   - insert(array $data): int
    //   - update(int $id, array $data): int
    //   - delete(int $id): void
    //
    // Comment no añade métodos propios porque el documento dice:
    // "Tampoco te indicamos si el modelo Comment necesita métodos adicionales
    //  o le basta con los que hereda de BaseModel. Nuevamente, decídelo tú".
    //
    // En este caso, se ha decidido que le basta con lo que hereda:
    // - Para insertar un comentario → Comment::insert([...]).
    // - Para leer los comentarios de un post → PostController::show() hace
    //   la consulta con Database::fetchAll() (con JOIN a users).
    //
    // Alternativa (también válida): añadir un método getByPostId(int $postId)
    // a este modelo y llamarlo desde PostController::show(). Pero no es
    // necesario, y el documento permite ambas opciones.

    /**
     * Constructor.
     * Llama al constructor de BaseModel pasándole el nombre de la tabla.
     * Así BaseModel sabe sobre qué tabla hacer las operaciones genéricas
     * (getAll, getById, insert, update, delete).
     */
    public function __construct()
    {
        parent::__construct('comments');
        // parent::__construct(...) invoca el constructor de la clase padre (BaseModel).
        // BaseModel guarda 'comments' en $this->tableName.
        // A partir de ahí, getAll() hará "SELECT * FROM comments", etc.
    }
}