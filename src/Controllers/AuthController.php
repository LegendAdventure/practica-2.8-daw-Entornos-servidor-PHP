<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Article;

class ArticleController
{
    // Mostrar todos los artículos
    public function index(): void
    {
        $articles = Article::all();
        // Carga la vista /Views/articles/index.php, encargada de mostrar el array $articles
        require __DIR__ . '/../Views/articles/index.php';
    }

    // Mostrar un artículo por $id
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $article = Article::find($id);

        if ($article === null) {
            http_response_code(404);
            echo 'Artículo no encontrado.';
            return;
        }
        // Carga la vista para mostrar artículos (show.php)
        require __DIR__ . '/../Views/articles/show.php';
    }

    // Formulario para crear un artículo
    public function create(): void
    {
        // Carga la vista con el formulario de crear artículos
        require __DIR__ . '/../Views/articles/create.php';
    }

    // Almacenar el artículo que viene del formulario de create()
    public function store(): void
    {
        $this->checkCsrfToken();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);

        if ($title === '') {
            $_SESSION['error'] = 'El título es obligatorio.';
            // Redirige al endpoint '/articles/create'
            header('Location: /articles/create');
            exit;
        }

        Article::create($title, $description, $price, $stock);

        $_SESSION['success'] = 'Artículo creado correctamente.';
        // Redirige al endpoint '/articles'
        header('Location: /articles');
        exit;
    }

    // Formulario para modificar un artículo
    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $article = Article::find($id);

        if ($article === null) {
            http_response_code(404);
            echo 'Artículo no encontrado.';
            return;
        }

        // Carga la vista con el formulario de editar artículos
        require __DIR__ . '/../Views/articles/edit.php';
    }

    // Almacenar el artículo que viene del formulario de edit()
    public function update(): void
    {
        $this->checkCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        $article = Article::find($id);

        if ($article === null) {
            http_response_code(404);
            echo 'Artículo no encontrado.';
            return;
        }

        $article->title = trim($_POST['title'] ?? '');
        $article->description = trim($_POST['description'] ?? '');
        $article->price = (float) ($_POST['price'] ?? 0);
        $article->stock = (int) ($_POST['stock'] ?? 0);

        $article->update();

        $_SESSION['success'] = 'Artículo actualizado correctamente.';
        header('Location: /articles');
        exit;
    }

    // Borrar un artículo
    public function delete(): void
    {
        $this->checkCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        Article::delete($id);

        $_SESSION['success'] = 'Artículo eliminado.';
        header('Location: /articles');
        exit;
    }

    // Comprobar el token CSRF. Se usa como método private auxiliar para no 
    // escribirlo varias veces a lo largo del controlador.
    private function checkCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }
}