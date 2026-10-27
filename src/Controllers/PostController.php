<?php

declare(strict_types=1);
// Modo estricto de tipos: si una función espera string y le pasas int,
// lanza TypeError en vez de convertirlo. Buena práctica.

namespace App\Controllers;
// Namespace de los controladores. Composer (PSR-4) lo busca en src/Controllers/.

use App\Core\Database;
// Importa el singleton PDO. Se usa en show() para la consulta de comentarios.

use App\Models\Post;
// Importa el modelo Post (hereda de BaseModel).

class PostController
{
    /**
     * Comprueba el token CSRF. Se llama al principio de cada acción POST
     * (store, update, destroy) para evitar ataques CSRF.
     */
    private function checkCsrfToken(): void
    {
        // Recoge el token del formulario. Si no viene, cadena vacía.
        $token = $_POST['csrf_token'] ?? '';

        // hash_equals compara en tiempo constante (timing-safe).
        // Si no coincide, 403 y die (mata la ejecución).
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }

    // ===============================================================
    // LECTURA (público, no requiere sesión)
    // ===============================================================

    /**
     * Listado de posts. Si hay búsqueda (?q=...), filtra por título o categoría.
     * Ruta: GET /posts
     */
    public function index(): void
    {
        // Instancia el modelo Post (aquí no hace falta sesión).
        $postModel = new Post();

        // Recoge el término de búsqueda del query string (?q=php).
        // trim() quita espacios sobrantes. Si no hay, cadena vacía.
        $searchTerm = trim($_GET['q'] ?? '');

        // Si hay término → search(). Si no → todos los posts con autor.
        if ($searchTerm !== '') {
            $posts = $postModel->search($searchTerm);
        } else {
            $posts = $postModel->getAllWithAuthors();
        }

        // Carga la vista. La vista puede usar $posts.
        require __DIR__ . '/../Views/posts/index.php';
    }

    /**
     * Detalle de un post, con sus comentarios.
     * Ruta: GET /posts/show/<id> (o /posts/show?id=N)
     */
    public function show(): void
    {
        // Lee el id. Con rutas limpias, Router::resolve() lo pone en $_GET['id'].
        // Con la forma antigua (?id=N) también funciona.
        $id = (int) ($_GET['id'] ?? 0);

        // Busca el post con el email del autor (JOIN con users).
        $postModel = new Post();
        $post = $postModel->getByIdWithAuthor($id);

        // Si no existe → 404 y salir.
        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;   // ← return, no exit: no redirigimos, solo paramos
        }

        // Carga los comentarios del post, con el email del autor de cada uno.
        // JOIN con users para no hacer N+1 consultas.
        // ORDER BY created_at ASC → los más antiguos primero (orden cronológico).
        $comments = Database::fetchAll(
            'SELECT comments.*, users.email AS author_email
             FROM comments
             INNER JOIN users ON users.id = comments.user_id
             WHERE comments.post_id = :post_id
             ORDER BY comments.created_at ASC',
            ['post_id' => $id]
        );

        // Carga la vista. La vista puede usar $post y $comments.
        require __DIR__ . '/../Views/posts/show.php';
    }

    // ===============================================================
    // CREAR (solo logueados)
    // ===============================================================

    /**
     * Muestra el formulario de creación.
     * Ruta: GET /posts/create
     */
    public function create(): void
    {
        // Si no hay sesión → redirect al login con mensaje flash.
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para publicar.';
            header('Location: /login');
            exit;   // imprescindible tras header
        }

        // Si hay sesión → carga el formulario.
        require __DIR__ . '/../Views/posts/create.php';
    }

    /**
     * Procesa el formulario de creación (POST).
     * Ruta: POST /posts/store
     */
    public function store(): void
    {
        // Primero CSRF: si el token no es válido, 403 y die.
        // Es lo primero porque no queremos procesar datos de un ataque CSRF.
        $this->checkCsrfToken();

        // Comprobación de sesión (defensa en profundidad: create() ya lo comprueba,
        // pero store() puede recibir un POST directo sin pasar por create()).
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para publicar.';
            header('Location: /login');
            exit;
        }

        // Recoge los datos del formulario.
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? '');

        // Validación: todos los campos son obligatorios.
        if ($title === '' || $body === '' || $category === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: /posts/create');
            exit;
        }

        // Inserta el post.
        // OJO: user_id sale de $_SESSION, NUNCA de $_POST.
        // Aunque un atacante añada <input name="user_id">, aquí no lo leemos.
        $postModel = new Post();
        $postModel->insert([
            'user_id'  => (int) $_SESSION['user_id'],
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        // Mensaje flash y redirect (patrón PRG: Post → Redirect → Get).
        // Así, si el usuario recarga (F5), no se reenvía el formulario.
        $_SESSION['success'] = 'Publicación creada correctamente.';
        header('Location: /posts');
        exit;
    }

    // ===============================================================
    // EDITAR (solo el autor)
    // ===============================================================

    /**
     * Muestra el formulario de edición de un post.
     * Ruta: GET /posts/edit/<id>
     */
    public function edit(): void
    {
        // 1. Comprobar sesión.
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        // 2. Leer id.
        $id = (int) ($_GET['id'] ?? 0);

        // 3. Buscar el post.
        $postModel = new Post();
        $post = $postModel->getById($id);

        // 4. Si no existe → 404.
        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        // 5. Comprobar autoría: si el post no es del usuario logueado → 403.
        // (int) para comparar como enteros, no como strings.
        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';   // vista de error
            return;   // ← salir sin cargar el formulario
        }

        // 6. Si es el autor → cargar el formulario con los datos del post.
        require __DIR__ . '/../Views/posts/edit.php';
    }

    /**
     * Procesa el formulario de edición (POST).
     * Ruta: POST /posts/update/<id>
     */
    public function update(): void
    {
        // 1. CSRF primero.
        $this->checkCsrfToken();

        // 2. Comprobar sesión.
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        // 3. Leer id (viene de $_POST['id'] si el router lo pone, o del form).
        $id = (int) ($_POST['id'] ?? 0);

        // 4. Buscar el post.
        $postModel = new Post();
        $post = $postModel->getById($id);

        // 5. Si no existe → 404.
        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        // 6. Comprobar autoría: si no es el autor → 403.
        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        // 7. Recoger y validar campos.
        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if ($title === '' || $body === '' || $category === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            // Redirige de vuelta al formulario de edición (con el id en la URL).
            header('Location: /posts/edit/' . $id);
            exit;
        }

        // 8. Actualizar.
        $postModel->update($id, [
            'title'    => $title,
            'body'     => $body,
            'category' => $category,
        ]);

        // 9. Mensaje flash y redirect al detalle (patrón PRG).
        $_SESSION['success'] = 'Publicación actualizada.';
        header('Location: /posts/show/' . $id);
        exit;
    }

    // ===============================================================
    // BORRAR (solo el autor)
    // ===============================================================

    /**
     * Borra un post (POST).
     * Ruta: POST /posts/delete/<id>
     */
    public function destroy(): void
    {
        // 1. CSRF primero.
        $this->checkCsrfToken();

        // 2. Comprobar sesión.
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión.';
            header('Location: /login');
            exit;
        }

        // 3. Leer id.
        $id = (int) ($_POST['id'] ?? 0);

        // 4. Buscar el post.
        $postModel = new Post();
        $post = $postModel->getById($id);

        // 5. Si no existe → 404.
        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;
        }

        // 6. Comprobar autoría: si no es el autor → 403.
        // Esto es CLAVE: sin esta comprobación, cualquier usuario logueado
        // podría borrar posts de otros. Es la autorización (no confundir con
        // autenticación, que es "saber quién eres").
        if ((int) $post['user_id'] !== (int) $_SESSION['user_id']) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            return;
        }

        // 7. Borrar.
        // Las FK con ON DELETE CASCADE hacen que se borren también
        // los comentarios asociados (sin dejar huérfanos).
        $postModel->delete($id);

        // 8. Mensaje flash y redirect (patrón PRG).
        $_SESSION['success'] = 'Publicación eliminada.';
        header('Location: /posts');
        exit;
    }
}