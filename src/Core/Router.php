<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    // Aquí guardaremos las rutas válidas de nuestra aplicación
    private array $routes = [];

    // Este método guarda las rutas por GET
    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    // Este método guarda las rutas por POST
    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    // Este método saca del array de rutas válidas el controlador y el método 
    // que hay que invocar para una ruta ($path) y un método ('GET' o 'POST') determinados
    public function dispatch(string $method, string $path): void
    {
        $action = $this->routes[$method][$path] ?? null; // ?? Devuelve lo de la izquieda (routes) o lo de la derecha (null)
  

        if ($action === null) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }

        // Extrae nombre del controlador y nombre del método
        [$controllerClass, $methodName] = $action;
        // Instancia el controlador
        $controller = new $controllerClass();
        // Invoca el método
        $controller->$methodName();
    }
}