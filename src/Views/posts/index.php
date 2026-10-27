<?php
/** @var array|null $posts */
// Comentario para Intelephense (el IDE). Le dice que $posts existe y es
// array o null. No afecta a la ejecución, solo evita el aviso de
// "Undefined variable $posts" en el editor.

// Incluye la cabecera común (DOCTYPE, <head>, <header>, flash, <main>).
// __DIR__ es src/Views/posts; /../layout.php sube a src/Views/layout.php.
require __DIR__ . '/../layout.php';
?>

<h2>Publicaciones</h2>

<!-- Formulario de búsqueda. method="get" envía el término en la URL (?q=...).
     action="/posts" envía a PostController::index().
     Al enviar, la URL queda como /posts?q=loquesea. -->
<form method="get" action="/posts" style="margin-bottom: 1rem;">
    <!-- Campo de búsqueda. value="..." repuebla el campo con el término actual
         (para que el usuario vea lo que buscó). htmlspecialchars evita XSS
         si el término contiene <, >, etc. -->
    <input type="text" name="q" placeholder="Buscar por título o categoría..."
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Buscar</button>
</form>

<?php
// Si hay sesión (usuario logueado), muestra el enlace para crear un post.
// Si no hay sesión, no se muestra (para eso está el condicional).
?>
<?php if (!empty($_SESSION['user_id'])): ?>
    <p><a href="/posts/create">+ Publicar anuncio</a></p>
<?php endif; ?>

<?php
// Si no hay posts (null) o el array está vacío, muestra un mensaje.
// $posts viene de PostController::index() (getAllWithAuthors o search).
// Database::fetchAll devuelve null si no hay filas.
?>
<?php if ($posts === null || count($posts) === 0): ?>
    <p>No hay publicaciones que mostrar.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php
        // Recorre cada post y pinta una tarjeta con título, categoría,
        // autor y fecha. Todo escapado con htmlspecialchars para evitar XSS.
        ?>
        <?php foreach ($posts as $post): ?>
            <li style="border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                <h3 style="margin-top: 0;">
                    <!-- Enlace al detalle con ruta limpia: /posts/show/<id>.
                         (int) castea el id a entero para evitar inyección. -->
                    <a href="/posts/show/<?= (int) $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                <p style="margin: 0.5rem 0;">
                    <!-- Categoría, autor y fecha del post. author_email viene
                         del JOIN con users en Post::getAllWithAuthors(). -->
                    <strong>Categoría:</strong> <?= htmlspecialchars($post['category']) ?><br>
                    <strong>Autor:</strong> <?= htmlspecialchars($post['author_email']) ?><br>
                    <strong>Fecha:</strong> <?= htmlspecialchars($post['created_at']) ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
// Incluye el pie común: cierra </main>, </body> y </html>.
require __DIR__ . '/../footer.php';
?>