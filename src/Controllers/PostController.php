<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;

class PostController
{
    // ---------------------------------------------------------------
    // Lectura (público)
    // ---------------------------------------------------------------

    // Listado de posts, con búsqueda opcional por título o categoría
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

    // Detalle de un post
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