<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Comment;

class CommentController
{
    private function checkCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }

    public function store(): void
    {
        $this->checkCsrfToken();

        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para comentar.';
            header('Location: /login');
            exit;
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        if ($postId <= 0) {
            $_SESSION['error'] = 'Publicación no válida.';
            header('Location: /posts');
            exit;
        }

        $post = Database::fetchOne(
            'SELECT id FROM posts WHERE id = :id',
            ['id' => $postId]
        );

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        if ($body === '') {
            $_SESSION['error'] = 'El comentario no puede estar vacío.';
            header('Location: /posts/show/' . $postId);
            exit;
        }

        $commentModel = new Comment();
        $commentModel->insert([
            'post_id' => $postId,
            'user_id' => (int) $_SESSION['user_id'],
            'body'    => $body,
        ]);

        $_SESSION['success'] = 'Comentario añadido.';
        header('Location: /posts/show/' . $postId);
        exit;
    }
}