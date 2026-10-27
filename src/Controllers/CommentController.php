<?php

declare(strict_types=1);
// declare(strict_types=1) obliga a que PHP sea estricto con los tipos:
// si una función espera un string y le pasas un int, lanza TypeError
// en lugar de convertirlo silenciosamente. Es buena práctica en proyectos modernos.

namespace App\Controllers;
// Indica que esta clase vive en el namespace App\Controllers.
// Gracias al autoload PSR-4 ("App\\": "src/"), Composer la busca en src/Controllers/.

use App\Core\Database;
// Importa la clase Database (el singleton PDO) para no tener que escribir
// \App\Core\Database cada vez que la usemos.

use App\Models\Comment;
// Importa el modelo Comment (hereda de BaseModel) para insertar comentarios.

class CommentController
{
    /**
     * Comprueba que el token CSRF del formulario coincide con el de la sesión.
     * Se llama al principio de cada acción que recibe un POST.
     */
    private function checkCsrfToken(): void
    {
        // Recoge el token que viene del formulario. Si no viene, cadena vacía.
        $token = $_POST['csrf_token'] ?? '';

        // hash_equals compara en tiempo constante (timing-safe).
        // Si el token no coincide, devuelve 403 y corta la ejecución.
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }

    /**
     * Guarda un comentario nuevo asociado a un post y al usuario logueado.
     * El post_id viene del formulario; el user_id, de la sesión (nunca del formulario).
     */
    public function store(): void
    {
        // 1. Primero el CSRF: si no es válido, no procesamos nada más.
        $this->checkCsrfToken();

        // 2. Comprobamos que hay sesión. Si no, redirigimos al login.
        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para comentar.';
            header('Location: /login');
            exit;   // MUY IMPORTANTE: exit después de header para que no siga ejecutando
        }

        // 3. Recogemos los datos del formulario.
        //    post_id es el post al que se comenta (no sensible: va en campo oculto).
        //    body es el texto del comentario.
        $postId = (int) ($_POST['post_id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        // 4. Validación básica: post_id debe ser un entero positivo.
        if ($postId <= 0) {
            $_SESSION['error'] = 'Publicación no válida.';
            header('Location: /posts');
            exit;
        }

        // 5. Comprobar que el post existe realmente en la BD.
        //    Evita que alguien envíe un post_id inventado (por ejemplo, 999).
        $post = Database::fetchOne(
            'SELECT id FROM posts WHERE id = :id',
            ['id' => $postId]
        );

        if ($post === null) {
            http_response_code(404);
            echo 'Publicación no encontrada.';
            return;   // return (no exit) porque no hay redirección, solo 404
        }

        // 6. Validación: el cuerpo del comentario no puede estar vacío.
        if ($body === '') {
            $_SESSION['error'] = 'El comentario no puede estar vacío.';
            header('Location: /posts/show/' . $postId);
            exit;
        }

        // 7. Insertar el comentario.
        //    OJO: user_id sale de $_SESSION, NUNCA de $_POST.
        //    Aunque alguien manipule el HTML y añada un <input name="user_id">,
        //    aquí no lo leemos: usamos el de la sesión. Esto evita suplantación.
        $commentModel = new Comment();
        $commentModel->insert([
            'post_id' => $postId,
            'user_id' => (int) $_SESSION['user_id'],
            'body'    => $body,
        ]);

        // 8. Mensaje flash de éxito y redirección al detalle del post.
        //    Post/Redirect/Get: tras un POST, redirigimos con GET para evitar
        //    que al recargar (F5) se reenvíe el formulario y se duplique el comentario.
        $_SESSION['success'] = 'Comentario añadido.';
        header('Location: /posts/show/' . $postId);
        exit;
    }
}