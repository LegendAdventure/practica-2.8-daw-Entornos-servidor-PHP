<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\Post;

class PostController
{
    private function checkCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }

    // ---------------------------------------------------------------
    // Lectura (público)
    // ---------------------------------------------------------------

    public function index(): void
    {
        $postModel = new Post();
        $searchTerm = trim($_GET['q'] ?? '');

        if ($searchTerm !== '') {
            $posts = $postModel->search($searchTerm);
        } else {
            $posts = $postModel->getAllWithAuthors();
        }

        require __DIR__ . '/../Views/posts/index.php';
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        $postModel = new Post();
        $post = $postModel->getByIdWithAuthor($id);

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        $comments = Database::fetchAll(
            'SELECT comments.*, users.email AS author_email
             FROM comments
             INNER JOIN users ON users.id = comments.user_id
             WHERE comments.post_id = :post_id
             ORDER BY comments.created_at ASC',
            ['post_id' => $id]
        );

        require __DIR__ . '/../Views/posts/show.php';
    }

    // ---------------------------------------------------------------
    // Crear (solo logueados)
    // ---------------------------------------------------------------

    public function create(): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para publicar.';
            header('Location: /login');
            exit;
        }

        require __DIR__ . '/../Views/posts/create.php';
    }

    public function store(): void
    {
        $this->checkCsrfToken();

        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para publicar.';
            header('Location: /login');
            exit;
        }

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if ($title === '' || $body === '' || $category === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: /posts/create');
            exit;
        }

        $postModel = new Post();
        $postModel->insert([
            'user_id'  => (int) $_SESSION['user_id'],
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        $_SESSION['success'] = 'Publicación creada correctamente.';
        header('Location: /posts');
        exit;
    }

    // ---------------------------------------------------------------
    // Editar (solo el autor)
    // ---------------------------------------------------------------

    public function edit(): void
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        $id = (int) ($_GET['id'] ?? 0);

        $postModel = new Post();
        $post = $postModel->getById($id);

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        require __DIR__ . '/../Views/posts/edit.php';
    }

    public function update(): void
    {
        $this->checkCsrfToken();

        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);

        $postModel = new Post();
        $post = $postModel->getById($id);

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if ($title === '' || $body === '' || $category === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: /posts/edit?id=' . $id);
            exit;
        }

        $postModel->update($id, [
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        $_SESSION['success'] = 'Publicación actualizada.';
        header('Location: /posts/show?id=' . $id);
        exit;
    }

    // ---------------------------------------------------------------
    // Borrar (solo el autor)
    // ---------------------------------------------------------------

    public function destroy(): void
    {
        $this->checkCsrfToken();

        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);

        $postModel = new Post();
        $post = $postModel->getById($id);

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        $postModel->delete($id);

        $_SESSION['success'] = 'Publicación eliminada.';
        header('Location: /posts');
        exit;
    }
}