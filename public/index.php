<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\ArticleController;

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$router = new Router();

// Registramos todas las rutas o endpoints a los que va a responder nuestra aplicación,
// junto con el nombre del controlador y el nombre del método a los que corresponden.
$router->get('/articles', [ArticleController::class, 'index']);
$router->get('/articles/create', [ArticleController::class, 'create']);
$router->post('/articles', [ArticleController::class, 'store']);
$router->get('/articles/edit', [ArticleController::class, 'edit']);
$router->post('/articles/update', [ArticleController::class, 'update']);
$router->post('/articles/delete', [ArticleController::class, 'delete']);

// Descomponemos la URL de entrada para extraer controlador y métodos
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';  // Saca URL
$method = $_SERVER['REQUEST_METHOD'];   // Saca verbo http (GET o POST)

// Si la URL es solo '/', saltamos a '/articles'
if ($path === '/') {
    header('Location: /articles');
    exit;
}

// Pasamos el control al enrutador para que llame al controlador y método correctos
$router->dispatch($method, $path);