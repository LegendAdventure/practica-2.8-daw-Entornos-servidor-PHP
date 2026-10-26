<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Post extends BaseModel
{
    public function __construct()
    {
        parent::__construct('posts');
    }

    /**
     * Devuelve todos los posts junto con el email del autor.
     * Usa un JOIN para no hacer N+1 consultas.
     */
    public function getAllWithAuthors(): ?array
    {
        $sql = 'SELECT posts.*, users.email AS author_email
                FROM posts
                INNER JOIN users ON users.id = posts.user_id
                ORDER BY posts.created_at DESC';

        return Database::fetchAll($sql);
    }

    /**
     * Devuelve un post concreto junto con el email del autor.
     */
    public function getByIdWithAuthor(int $id): ?array
    {
        $sql = 'SELECT posts.*, users.email AS author_email
                FROM posts
                INNER JOIN users ON users.id = posts.user_id
                WHERE posts.id = :id';

        return Database::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Busca posts cuyo título o categoría contengan el término indicado.
     * Devuelve también el email del autor.
     */
        /**
     * Busca posts cuyo título o categoría contengan el término indicado.
     * Devuelve también el email del autor.
     */
    public function search(string $term): ?array
    {
        $sql = 'SELECT posts.*, users.email AS author_email
                FROM posts
                INNER JOIN users ON users.id = posts.user_id
                WHERE posts.title LIKE :title_term OR posts.category LIKE :category_term
                ORDER BY posts.created_at DESC';

        $pattern = '%' . $term . '%';

        return Database::fetchAll($sql, [
            'title_term'    => $pattern,
            'category_term' => $pattern,
        ]);
    }
}