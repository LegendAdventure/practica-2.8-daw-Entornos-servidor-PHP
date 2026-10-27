<?php

declare(strict_types=1);
// Modo estricto de tipos: si una función espera string y le pasas int,
// lanza TypeError en vez de convertirlo. Buena práctica.

namespace App\Core;
// Namespace del núcleo. Composer (PSR-4) lo busca en src/Core/.

class Router
{
    /**
     * Array de rutas registradas.
     * Estructura:
     *   $routes['GET']['/posts']  = [PostController::class, 'index']
     *   $routes['POST']['/login'] = [AuthController::class, 'login']
     *
     * Primer nivel: método HTTP ('GET', 'POST').
     * Segundo nivel: path ('/posts', '/login').
     * Valor: [Clase, método] a ejecutar.
     */
    private array $routes = [];

    /**
     * Registra una ruta GET.
     * No la ejecuta, solo la guarda para cuando llegue la petición.
     */
    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    /**
     * Registra una ruta POST.
     * Igual que get(), pero para peticiones POST (formularios).
     */
    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    /**
     * Traduce una URL con id al final (/posts/show/3) a la ruta base
     * (/posts/show) y guarda el id en $_GET['id'] o $_POST['id'].
     *
     * Si la URL no sigue el patrón, la devuelve tal cual.
     *
     * Ejemplos:
     *   /posts/show/3    → devuelve '/posts/show'    y $_GET['id'] = 3
     *   /posts/edit/3    → devuelve '/posts/edit'    y $_GET['id'] = 3
     *   /posts/delete/3  → devuelve '/posts/delete'  y $_POST['id'] = 3
     *   /posts           → devuelve '/posts'          (sin cambios)
     *   /login           → devuelve '/login'          (sin cambios)
     */
    public function resolve(string $path): string
    {
        // preg_match busca el patrón /letras/letras/NUMERO en el path.
        // Desglose del patrón '#^(/[a-z]+/[a-z]+)/(\d+)$#i':
        //   ^                → principio del string
        //   (                → inicio del grupo 1
        //     /[a-z]+        → barra + una o más letras (ej: /posts)
        //     /[a-z]+        → barra + una o más letras (ej: /show)
        //   )                → fin del grupo 1
        //   /                → barra separadora
        //   (                → inicio del grupo 2
        //     \d+            → uno o más dígitos (el id)
        //   )                → fin del grupo 2
        //   $                → fin del string
        //   #i               → flag i: case-insensitive
        //
        // $matches[0] = todo el match (ej: /posts/show/3)
        // $matches[1] = grupo 1 (ej: /posts/show)
        // $matches[2] = grupo 2 (ej: 3)
        if (preg_match('#^(/[a-z]+/[a-z]+)/(\d+)$#i', $path, $matches)) {
            // Convierte el id a entero.
            $id = (int) $matches[2];

            // Si la petición es POST, el id va a $_POST.
            // Si es GET, el id va a $_GET.
            // ¿Por qué? Porque:
            //   - show() y edit() leen $_GET['id'] (son GET).
            //   - destroy() y update() leen $_POST['id'] (son POST).
            // Así no hay que tocar los controladores.
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $_POST['id'] = $id;
            } else {
                $_GET['id'] = $id;
            }

            // Devuelve la ruta base (sin el id) para que dispatch() la busque.
            return $matches[1];
        }

        // Si no coincide con el patrón, devuelve el path tal cual.
        return $path;
    }

    /**
     * Ejecuta la acción correspondiente a un método HTTP y un path.
     *
     * 1. Busca la acción en $routes.
     * 2. Si no existe → 404.
     * 3. Si existe → instancia el controlador y llama al método.
     */
    public function dispatch(string $method, string $path): void
    {
        // Busca la acción para el método y path indicados.
        // ?? null → si no existe la clave, devuelve null (sin warning).
        $action = $this->routes[$method][$path] ?? null;

        // Si no hay acción registrada para esa ruta → 404.
        if ($action === null) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;   // sale del método
        }

        // Desestructura el array [Clase, método] en dos variables.
        // $controllerClass = 'App\Controllers\PostController'
        // $methodName      = 'index'
        [$controllerClass, $methodName] = $action;

        // Instancia el controlador.
        // new $controllerClass() → new PostController() (variable como nombre de clase).
        $controller = new $controllerClass();

        // Llama al método.
        // $controller->$methodName() → $controller->index() (variable como nombre de método).
        $controller->$methodName();
    }
}