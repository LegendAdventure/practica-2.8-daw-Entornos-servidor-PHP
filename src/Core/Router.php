<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function resolve(string $path): string
    {
        if (preg_match('#^(/[a-z]+/[a-z]+)/(\d+)$#i', $path, $matches)) {
            $id = (int) $matches[2];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $_POST['id'] = $id;
            } else {
                $_GET['id'] = $id;
            }

            return $matches[1];
        }

        return $path;
    }

    public function dispatch(string $method, string $path): void
    {
        $action = $this->routes[$method][$path] ?? null;

        if ($action === null) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }

        [$controllerClass, $methodName] = $action;
        $controller = new $controllerClass();
        $controller->$methodName();
    }
}