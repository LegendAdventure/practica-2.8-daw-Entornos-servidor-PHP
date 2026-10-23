<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AuthController;

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$router = new Router();

$router->get('/register',  [AuthController::class, 'showRegisterForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login',     [AuthController::class, 'showLoginForm']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/logout',    [AuthController::class, 'logout']);

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($path === '/') {
    require __DIR__ . '/../src/Views/layout.php';
    echo '<h2>Bienvenido al tablón de anuncios</h2>';
    echo '<p>El listado de publicaciones llegará en el Paso 4.</p>';
    require __DIR__ . '/../src/Views/footer.php';
    exit;
}

$router->dispatch($method, $path);