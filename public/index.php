<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\PostController;

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$router = new Router();

// --- Autenticación ---
$router->get('/register',  [AuthController::class, 'showRegisterForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login',     [AuthController::class, 'showLoginForm']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/logout',    [AuthController::class, 'logout']);

// --- Posts (Paso 4) ---
// --- Posts ---
$router->get('/posts',         [PostController::class, 'index']);
$router->get('/posts/show',    [PostController::class, 'show']);
$router->get('/posts/create',  [PostController::class, 'create']);
$router->post('/posts/store',  [PostController::class, 'store']);
$router->get('/posts/edit',    [PostController::class, 'edit']);
$router->post('/posts/update', [PostController::class, 'update']);
$router->post('/posts/delete', [PostController::class, 'destroy']);

// Home = listado de posts
$router->get('/', [PostController::class, 'index']);

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $path);